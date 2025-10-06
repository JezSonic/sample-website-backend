<?php

namespace App\Utils\Services;

use App\Exceptions\Auth\OAuth\OAuthAccountPasswordLoginException;
use App\Exceptions\Auth\TwoFactor\TwoFactorRequiredException;
use App\Models\User;
use App\Models\UserProfileSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laragear\TwoFactor\Facades\Auth2FA;

class AuthService {
    /**
     * Register a new user
     *
     * @param string $email The user's email
     * @param string $password The user's password
     * @param string $name The user's name
     * @return User The created user
     */
    public static function register(string $email, string $password, string $name): User {
        $user = new User();
        $salt = Str::random();
        $hashed = Hash::make($password . $salt);
        $user->password = $hashed;
        $user->salt = $salt;
        $user->email = $email;
        $user->name = $name;
        $user->save();

        $user_profile_settings = new UserProfileSettings();
        $user_profile_settings->user_id = $user->id;
        $user_profile_settings->save();

        return $user;
    }

    /**
     * Attempt to login a user with email and password
     *
     * @param string $email The user's email
     * @param string $password The user's password
     * @param Request $request The request object
     * @return array|null The user and tokens if login was successful, null otherwise
     * @throws OAuthAccountPasswordLoginException If the account was created using OAuth
     * @throws TwoFactorRequiredException
     */
    public static function login(string $email, string $password, Request $request): ?array {
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $data = $request->all();
        $user = User::where('email', '=', $email)->first();

        if ($user == null) {
            return null;
        }

        if ($user->hasTwoFactorEnabled() && $data['two_factor_code'] == null) {
            throw new TwoFactorRequiredException();
        } else if ($user->hasTwoFactorEnabled() && $data['two_factor_code'] != null) {
            if (!$user->validateTwofactorCode($data['two_factor_code'])) {
                return null;
            }
        }

        $_salt = $user->getSalt();
        // Check if an account was created using OAuth (no salt means OAuth account)
        if ($_salt === null) {
            throw new OAuthAccountPasswordLoginException();
        }
        $attempt = Auth::attempt(['email' => $email, 'password' => $password . $_salt]);

        if (!$attempt) {
            return null;
        }

        $request->session()->start();

        // Log login activity
        $ip_address = $request->ip();
        UserActivityService::logLoginActivity(
            $user->id,
            $ip_address,
            $request->userAgent(),
            'email'
        );

        // Create access token (expires in 1 hour)
        $accessToken = TokenService::createAccessToken(Auth::user());

        // Create refresh token (expires in 30 days)
        $refreshToken = TokenService::createRefreshToken(Auth::user());

        return [
            'id' => $user->id,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600 // 1 hour in seconds
        ];
    }

    /**
     * Logout a user
     *
     * @param Request $request The request object
     * @return bool True if logout was successful
     */
    public static function logout(Request $request): bool {
        TokenService::revokeAllTokens();
        $request->session()->invalidate();
        return true;
    }

    /**
     * Process OAuth login and generate tokens
     *
     * @param array $userData The user data from OAuth
     * @param Request $request The request object
     * @param string $loginMethod The login method (oauth_github, oauth_google, etc.)
     * @return array The tokens and user ID
     */
    public static function processOAuthLogin(array $userData, Request $request, string $loginMethod): array {
        $user = $userData['user'];
        Auth::login($user);

        // Log login activity
        UserActivityService::logLoginActivity(
            $user->id,
            $request->ip(),
            $request->userAgent(),
            $loginMethod
        );

        // Create access token (expires in 1 hour)
        $accessToken = TokenService::createAccessToken(Auth::user());

        // Create refresh token (expires in 30 days)
        $refreshToken = TokenService::createRefreshToken(Auth::user());

        return [
            'id' => $user->id,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600 // 1 hour in seconds
        ];
    }
}
