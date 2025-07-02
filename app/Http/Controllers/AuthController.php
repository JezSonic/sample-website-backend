<?php

namespace App\Http\Controllers;

use App\Exceptions\Auth\OAuth\UnsupportedDriver;
use App\Exceptions\Auth\OAuth\OAuthAccountPasswordLoginException;
use App\Exceptions\User\InvalidRefreshTokenException;
use App\Exceptions\User\InvalidTokenException;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\OAuthCallbackRequest;
use App\Http\Requests\OAuthRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyPasswordResetTokenRequest;
use App\Mail\ResetPassword;
use App\Models\GitHubUserData;
use App\Models\GoogleUserData;
use App\Models\User;
use App\Models\UserLoginActivity;
use App\Models\UserProfileSettings;
use App\Utils\Enums\OAuthDrivers;
use App\Utils\Services\IpLocationService;
use App\Utils\Traits\Response;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller {
    use Response;

    private array $supported_drivers = [OAuthDrivers::GITHUB, OAuthDrivers::GOOGLE];

    function register(RegisterRequest $request): JsonResponse {
        $data = $request->all();
        $user = new User();
        $salt = Str::random();
        $hashed = Hash::make($data['password'] . $salt);
        $user->password = $hashed;
        $user->salt = $salt;
        $user->email = $data['email'];
        $user->name = $data['name'];
        $user->save();
        $user_profile_settings = new UserProfileSettings();
        $user_profile_settings->user_id = $user->id;
        $user_profile_settings->save();
        return $this->boolResponse(true);
    }

    /**
     * @throws UnsupportedDriver
     */
    function callback(OAuthCallbackRequest $request, OAuthDrivers $driver): JsonResponse {
        function token_expiration(mixed $expires_in): int|null {
            if ($expires_in == null) {
                return null;
            }
            return time() + intval($expires_in);
        }

        $request->session()->regenerate(true);
        $this->checkDriver($driver);
        $userData = Socialite::driver($driver->value)->stateless()->user();
        $data = [
            'name' => $userData->name,
            'email' => $userData->email
        ];
        $user_id = User::where('email', '=', $userData->email)->first()->id ?? null;
        if ($user_id == null) {
            $new_user = new User();
            $new_user->name = $userData->name;
            $new_user->email = $userData->email;
            $new_user->save();
            $user_id = User::where('email', '=', $userData->email)->first()->id;
            $user_profile_settings = new UserProfileSettings();
            $user_profile_settings->user_id = $user_id;
            $user_profile_settings->save();
        }

        if ($driver == OAuthDrivers::GITHUB) {
            if ($data['email'] == $userData->user['email']) {
                $data['email_verified_at'] = now();
            }

            GitHubUserData::updateOrCreate(
                [
                    'user_id' => $user_id
                ], [
                'id' => $userData->user['id'],
                'github_login' => $userData->user['login'],
                'github_avatar_url' => $userData->user['avatar_url'],
                'github_gravatar_id' => $userData->user['gravatar_id'],
                'github_url' => $userData->user['url'],
                'github_html_url' => $userData->user['html_url'],
                'github_followers_url' => $userData->user['followers_url'],
                'github_following_url' => $userData->user['following_url'],
                'github_gists_url' => $userData->user['gists_url'],
                'github_starred_url' => $userData->user['starred_url'],
                'github_subscriptions_url' => $userData->user['subscriptions_url'],
                'github_organizations_url' => $userData->user['organizations_url'],
                'github_repos_url' => $userData->user['repos_url'],
                'github_events_url' => $userData->user['events_url'],
                'github_received_events_url' => $userData->user['received_events_url'],
                'github_type' => $userData->user['type'],
                'github_user_view_type' => $userData->user['user_view_type'],
                'github_site_admin' => $userData->user['site_admin'],
                'github_name' => $userData->user['name'],
                'github_company' => $userData->user['company'],
                'github_blog' => $userData->user['blog'],
                'github_location' => $userData->user['location'],
                'github_email' => $userData->user['email'],
                'github_hireable' => $userData->user['hireable'],
                'github_bio' => $userData->user['bio'],
                'github_twitter_username' => $userData->user['twitter_username'],
                'github_notification_email' => $userData->user['notification_email'],
                'public_repos' => $userData->user['public_repos'],
                'public_gists' => $userData->user['public_gists'],
                'public_followers' => $userData->user['followers'],
                'public_following' => $userData->user['following'],
                'github_refresh_token' => $userData->refreshToken,
                'github_token' => $userData->token,
                'github_token_expires_in' => token_expiration($userData->expiresIn),
            ]);
        } else if ($driver == OAuthDrivers::GOOGLE) {
            GoogleUserData::updateOrCreate(
                [
                    'user_id' => $user_id
                ], [
                'id' => $userData->id,
                'google_token' => $userData->token,
                'google_refresh_token' => $userData->refreshToken,
                'google_name' => $userData->name,
                'google_email' => $userData->email,
                'google_avatar_url' => $userData->avatar,
                'google_token_expires_in' => token_expiration($userData->expiresIn),
            ]);
            $email_verified = $userData->user['email_verified'] || $userData->user['verified_email'];
            if ($data['email'] == $userData->user['email'] && $email_verified) {
                $data['email_verified_at'] = now();
            }
        }

        $user = User::updateOrCreate([
            'email' => $userData->email,
        ], $data);
        Auth::login($user);

        // Log login activity
        $ip_address = $data['ip_address'] ?? $request->ip();
        $location = IpLocationService::getLocationFromIp($ip_address);

        $activity = UserLoginActivity::create([
            'user_id' => $user->id,
            'ip_address' => $ip_address,
            'location' => $location,
            'user_agent' => $request->userAgent(),
            'login_method' => 'oauth_' . $driver->value,
        ]);
        $activity->save();

        // Create access token (expires in 1 hour)

        //@TODO: Create a job to clear leftover tokens
        $accessToken = Auth::user()->createToken('access_token', ['*'], now()->addHour());

        // Create refresh token (expires in 30 days)
        $refreshToken = Auth::user()->createToken('refresh_token', ['refresh'], now()->addDays(30));

        //@TODO: Create a job to clear leftover tokens

        return response()->json([
            'id' => $user->id,
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600 // 1 hour in seconds
        ]);
    }

    /**
     * @throws UnsupportedDriver
     */
    function checkDriver(OAuthDrivers $driver): void {
        $array = array_column(OAuthDrivers::cases(), 'value');
        if (!in_array($driver->value, $array)) {
            throw new UnsupportedDriver('Unsupported OAuth driver. Supported drivers are: ' . join(', ', $array));
        }
    }

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     * @throws OAuthAccountPasswordLoginException
     * @noinspection PhpUnitAnnotationToAttributeInspection
     * @uses         User::getSalt()
     */
    function login(LoginRequest $request): JsonResponse {
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $user = new User();
        $data = $request->all();
        $user_check = $user::where('email', '=', $data['email'])->first();
        if ($user_check == null) {
            return $this->invalidCredentialsResponse();
        }

        $_salt = $user_check->getSalt();
        // Check if account was created using OAuth (no salt means OAuth account)
        if ($_salt === null) {
            throw new OAuthAccountPasswordLoginException();
        }

        $attempt = Auth::attempt(['email' => $data['email'], 'password' => $data['password'] . $_salt]);
        if (!$attempt) {
            return $this->invalidCredentialsResponse();
        }

        $request->session()->start();

        // Log login activity
        $ip_address = $data['ip_address'] ?? $request->ip();
        $location = IpLocationService::getLocationFromIp($ip_address);

        $activity = UserLoginActivity::create([
            'user_id' => $user_check->id,
            'ip_address' => $ip_address,
            'location' => $location,
            'user_agent' => $request->userAgent(),
            'login_method' => 'email'
        ]);
        $activity->save();

        //@TODO: Create a job to clear leftover tokens

        // Create access token (expires in 1 hour)
        $accessToken = Auth::user()->createToken('access_token', ['*'], now()->addHour());

        // Create refresh token (expires in 30 days)

        //@TODO: Create a job to clear leftover tokens
        $refreshToken = Auth::user()->createToken('refresh_token', ['refresh'], now()->addDays(30));

        return response()->json([
            'id' => $user_check->id,
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600 // 1 hour in seconds
        ]);
    }

    /**
     * @throws UnsupportedDriver
     */
    function oauth(OAuthRequest $request, OAuthDrivers $driver): JsonResponse {
        $this->checkDriver($driver);
        if ($driver->value == OAuthDrivers::GOOGLE->value) {
            return response()->json([
                    /**
                     * Target URL for OAuth login
                     */
                    'content' => Socialite::driver($driver->value)->with(array_merge([
                        'access_type' => 'offline',
                        'prompt' => 'consent',
                    ], [
                        'state' => 'integration_id=' . $request->input('integration_id', '')
                    ]))->redirect()->getTargetUrl()
                ]
            );
        } else if ($driver->value == OAuthDrivers::GITHUB->value) {
            return response()->json([
                /**
                 * Target URL for OAuth login
                 */
                'content' => Socialite::driver($driver->value)->scopes(['user'])->redirect()->getTargetUrl()
            ]);
        }
        return response()->json([
                /**
                 * Target URL for OAuth login
                 */
                'content' => Socialite::driver($driver->value)->redirect()->getTargetUrl()
            ]
        );
    }

    function logout(Request $request): JsonResponse {
        Auth::user()?->tokens()->delete();
        $request->session()->invalidate();
        return $this->boolResponse(true)->withoutCookie('newdev_token');
    }

    /**
     * Refresh access token using refresh token
     * @throws InvalidRefreshTokenException
     */
    function refreshToken(Request $request): JsonResponse {
        $request->validate([
            'refresh_token' => 'required|string'
        ]);

        $refreshToken = $request->input('refresh_token');

        // Find the refresh token in the database
        $tokenModel = PersonalAccessToken::findToken($refreshToken);

        if (!$tokenModel || $tokenModel->name !== 'refresh_token') {
            throw new InvalidRefreshTokenException();
        }

        // Check if refresh token is expired
        if ($tokenModel->expires_at && $tokenModel->expires_at->isPast()) {
            $tokenModel->delete();
            throw new InvalidRefreshTokenException();
        }

        // Check if refresh token has the correct ability
        if (!$tokenModel->can('refresh')) {
            throw new InvalidRefreshTokenException();
        }

        $user = $tokenModel->tokenable;

        // Delete old access tokens (keep refresh tokens)
        $user->tokens()->where('name', 'access_token')->delete();

        //@TODO: Create a job to clear leftover tokens
        $accessToken = $user->createToken('access_token', ['*'], now()->addHour());
        $tokenModel->forceFill(['last_used_at' => now()])->save();
        return response()->json([
            'access_token' => $accessToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600 // 1 hour in seconds
        ]);
    }

    /**
     * Revoke a specific refresh token
     * @throws InvalidRefreshTokenException
     */
    function revokeRefreshToken(Request $request): JsonResponse {
        $request->validate([
            'refresh_token' => 'required|string'
        ]);

        $refreshToken = $request->input('refresh_token');

        // Find the refresh token in the database
        $tokenModel = PersonalAccessToken::findToken($refreshToken);

        if (!$tokenModel || $tokenModel->name !== 'refresh_token') {
            throw new InvalidRefreshTokenException();
        }

        // Delete the refresh token
        $tokenModel->delete();
        return $this->boolResponse(true)->withoutCookie('newdev_token');
    }

    /**
     * @throws UnsupportedDriver
     */
    function revokeOAuth(Request $request, OAuthDrivers $driver): JsonResponse {
        $this->checkDriver($driver);
        $user = Auth::user();
        if ($driver->value == OAuthDrivers::GOOGLE->value) {
            $google_data = $user->googleData()->first();
            $google_data->delete();
        } else if ($driver->value == OAuthDrivers::GITHUB->value) {
            $github_data = $user->gitHubData()->first();
            $github_data->delete();
        }
        return $this->boolResponse(true);
    }

    public function requestChangePassword(ChangePasswordRequest $request): JsonResponse {
        $email = $request->only('email');
        $user = User::where('email', '=', $email)->first();
        function generate_token(User $user): string {
            $_token = bin2hex(random_bytes(16));
            $user->password_reset_token = $_token;
            $user->password_reset_token_valid_for = now()->addMinutes(15);
            $user->save();
            return $_token;
        }

        if ($user->password_reset_token != null) {
            if (time() < strtotime($user->password_reset_token_valid_for)) {
                $valid_until = $user->password_reset_token_valid_for;
                $token = $user->password_reset_token;
            } else {
                $token = generate_token($user);
                $valid_until = now()->addMinutes(15);
            }
        } else {
            $token = generate_token($user);
            $valid_until = now()->addMinutes(15);
        }

        $resetPasswordUrl = env("APP_DOMAIN") . '/auth/reset-password/' . $token;
        Mail::to($email)->send(new ResetPassword($resetPasswordUrl, $valid_until));
        return $this->boolResponse(true);
    }

    /**
     * @throws InvalidTokenException
     */
    public function changePassword(Request $request): JsonResponse {
        $data = $request->all();
        $user = User::where('password_reset_token', '=', $data['token'])->first();
        if ($user == null) {
            throw new InvalidTokenException();
        }
        $salt = Str::random();
        $hashed = Hash::make($data['password'] . $salt);
        $user->password = $hashed;
        $user->salt = $salt;
        $user->password_reset_token = null;
        $user->password_reset_token_valid_for = null;
        $user->save();
        return $this->boolResponse(true);
    }

    /**
     * @throws InvalidTokenException
     */
    public function verifyPasswordResetToken(VerifyPasswordResetTokenRequest $request): JsonResponse {
        $token = $request->only('token');
        $user = User::where('password_reset_token', '=', $token)->first();
        if ($user == null) {
            throw new InvalidTokenException();
        } else {
            if (time() > strtotime($user->password_reset_token_valid_for)) {
                $is_creating_password = ($user->getSalt() == null);
                $user->password_reset_token = null;
                $user->password_reset_token_valid_for = null;
                $user->save();
                return response()->json(['content' => false, 'creating_password' => $is_creating_password]);
            }
        }

        $is_creating_password = ($user->getSalt() == null);
        return response()->json(['content' => true, 'creating_password' => $is_creating_password]);
    }
}
