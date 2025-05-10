<?php

namespace App\Http\Controllers;

use App\Exceptions\Auth\OAuth\UnsupportedDriver;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\GitHubUserData;
use App\Models\GoogleUserData;
use App\Models\User;
use App\Utils\Enums\OAuthDrivers;
use App\Utils\Traits\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
        return $this->boolResponse(true);
    }

    /**
     * @throws UnsupportedDriver
     */
    function callback(Request $request, OAuthDrivers $driver): JsonResponse {
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
        }

        if ($driver == OAuthDrivers::GITHUB) {
            GitHubUserData::updateOrCreate(
                [
                    'user_id' => $user_id
                ],
                [
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
                ],
                [
                    'id' => $userData->id,
                    'google_token' => $userData->token,
                    'google_refresh_token' => $userData->refreshToken,
                    'google_name' => $userData->name,
                    'google_email' => $userData->email,
                    'google_avatar_url' => $userData->avatar,
                    'google_token_expires_in' => token_expiration($userData->expiresIn),
                ]);
        }

        $user = User::updateOrCreate([
            'email' => $userData->email,
        ], $data);
        Auth::login($user);
        return response()->json(['content' => $user->id, 'token' => Auth::user()->createToken('authToken')->plainTextToken]);
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
     * @noinspection PhpUnitAnnotationToAttributeInspection
     * @uses User::getSalt()
     */
    function login(LoginRequest $request): JsonResponse {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $user = new User();
        $data = $request->all();
        $user_check = $user::where('email', '=', $data['email'])->first();
        if ($user_check == null) {
            return $this->invalidCredentialsResponse();
        }

        $attempt = Auth::attempt(['email' => $data['email'], 'password' => $data['password'] . $user_check->getSalt()]);
        if (!$attempt) {
            return $this->invalidCredentialsResponse();
        }

        $request->session()->start();
        return response()->json(['content' => $user_check->id, 'token' => Auth::user()->createToken('authToken')->plainTextToken]);
    }

    /**
     * @throws UnsupportedDriver
     */
    function oauth(Request $request, OAuthDrivers $driver): JsonResponse {
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
                        'state' => 'integration_id='.$request->input('integration_id', '')
                    ]))->redirect()->getTargetUrl()
                ]
            );
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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->flush();
        return $this->boolResponse(true)->withoutCookie('newdev_token');
    }
}
