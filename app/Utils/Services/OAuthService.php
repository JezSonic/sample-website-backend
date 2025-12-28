<?php

namespace App\Utils\Services;

use App\Exceptions\Auth\OAuth\UnsupportedDriver;
use App\Models\GitHubUserData;
use App\Models\GoogleUserData;
use App\Models\User;
use App\Models\UserProfileSettings;
use App\Utils\Enums\OAuthDrivers;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class OAuthService {
    /**
     * Get the OAuth redirect URL
     *
     * @param OAuthDrivers $driver The OAuth driver to use
     * @param string|null $integrationId Optional integration ID for state parameter
     * @return string The OAuth redirect URL
     * @throws UnsupportedDriver If the driver is not supported
     */
    public static function getOAuthRedirectUrl(OAuthDrivers $driver, ?string $integrationId = null): string {
        self::checkDriver($driver);

        if ($driver->value == OAuthDrivers::GOOGLE->value) {
            $params = [
                'access_type' => 'offline',
                'prompt' => 'consent',
            ];

            if ($integrationId) {
                $params['state'] = 'integration_id=' . $integrationId;
            }

            return Socialite::driver($driver->value)
                ->with($params)
                ->redirect()
                ->getTargetUrl();
        } else if ($driver->value == OAuthDrivers::GITHUB->value) {
            return Socialite::driver($driver->value)
                ->scopes(['user'])
                ->redirect()
                ->getTargetUrl();
        }

        return Socialite::driver($driver->value)
            ->redirect()
            ->getTargetUrl();
    }

    /**
     * Check if the OAuth driver is supported
     *
     * @param OAuthDrivers $driver The OAuth driver to check
     * @return bool True if the driver is supported
     * @throws UnsupportedDriver If the driver is not supported
     */
    public static function checkDriver(OAuthDrivers $driver): bool {
        if (OAuthDrivers::tryFrom($driver->value) == null) {
            throw new UnsupportedDriver();
        }
        return true;
    }

    /**
     * Process OAuth callback and create or update user
     *
     * @param OAuthDrivers $driver The OAuth driver used
     * @param Request $request The request object
     * @return array User data and tokens
     * @throws UnsupportedDriver If the driver is not supported
     */
    public static function processOAuthCallback(OAuthDrivers $driver, Request $request): array {
        self::checkDriver($driver);

        if ($driver == OAuthDrivers::GOOGLE_ONE_TAP) {
            $userData = Socialite::driver('google-one-tap')->userFromToken($request->get('token'));
        } else {
            $userData = Socialite::driver($driver->value)->stateless()->user();
        }

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
                'github_token_expires_in' => self::calculateTokenExpiration($userData->expiresIn),
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
                'google_token_expires_in' => self::calculateTokenExpiration($userData->expiresIn),
            ]);

            $email_verified = $userData->user['email_verified'] || $userData->user['verified_email'];
            if ($data['email'] == $userData->user['email'] && $email_verified) {
                $data['email_verified_at'] = now();
            }
        } else if ($driver == OAuthDrivers::GOOGLE_ONE_TAP) {
            GoogleUserData::updateOrCreate(
                [
                    'user_id' => $user_id
                ], [
                'id' => $userData->id,
                'google_token' => null,
                'google_refresh_token' => null,
                'google_name' => $userData->name,
                'google_email' => $userData->email,
                'google_avatar_url' => $userData->avatar,
                'google_token_expires_in' => null,
            ]);

            $email_verified = $userData->user['email_verified'] || $userData->user['verified_email'];
            if ($data['email'] == $userData->user['email'] && $email_verified) {
                $data['email_verified_at'] = now();
            }
        }

        $user = User::updateOrCreate([
            'email' => $userData->email,
        ], $data);

        return [
            'user' => $user,
            'user_id' => $user->id,
            'oauth_data' => $userData
        ];
    }

    /**
     * Calculate token expiration timestamp
     *
     * @param mixed $expiresIn Expiration time in seconds
     * @return int|null Expiration timestamp or null if no expiration
     */
    public static function calculateTokenExpiration(mixed $expiresIn): ?int {
        if ($expiresIn == null) {
            return null;
        }
        return time() + intval($expiresIn);
    }

    /**
     * Revoke OAuth access for a user
     *
     * @param OAuthDrivers $driver The OAuth driver to revoke
     * @param User $user The user to revoke OAuth access for
     * @return bool True if the access was revoked successfully
     * @throws UnsupportedDriver If the driver is not supported
     */
    public static function revokeOAuth(OAuthDrivers $driver, User $user): bool {
        self::checkDriver($driver);

        if ($driver->value == OAuthDrivers::GOOGLE->value) {
            $google_data = $user->googleData()->first();
            if ($google_data) {
                $google_data->delete();
            }
        } else if ($driver->value == OAuthDrivers::GITHUB->value) {
            $github_data = $user->gitHubData()->first();
            if ($github_data) {
                $github_data->delete();
            }
        }

        return true;
    }
}
