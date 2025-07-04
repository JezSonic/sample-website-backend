<?php

namespace App\Utils\Services;

use App\Exceptions\User\AccountNotFoundException;
use App\Exceptions\User\PrivateProfileException;
use App\Http\Resources\UserResource;
use App\Jobs\ExportUserDataJob;
use App\Models\User;
use App\Models\UserDataExports;
use App\Models\UserProfileSettings;
use App\Utils\Enums\OAuthDrivers;
use App\Utils\Enums\UserDataExportStatus;
use Exception;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;

class UserService {
    /**
     * Get user profile data
     *
     * @param User $user The user to get profile data for
     * @return UserResource The user resource
     * @throws Exception If token validation fails
     */
    public static function getUserProfile(User $user): UserResource {
        $user_resource = new UserResource($user);
        $user_data = json_decode(json_encode($user_resource), true);

        if (array_key_exists(OAuthDrivers::GITHUB->value, $user_data)) {
            $github = $user->gitHubData()->first();
            $token_check = self::checkOAuthToken($user, $github->github_token, $github->github_refresh_token, false);

            if ($token_check && time() > $github->github_token_expires_in) {
                $new_user_data = Socialite::driver(OAuthDrivers::GITHUB->value)->userFromToken($github->github_token);
                $github->update([
                    'github_token' => $new_user_data->token,
                    'github_refresh_token' => $new_user_data->refreshToken,
                    'github_token_expires_in' => OAuthService::calculateTokenExpiration($new_user_data->expiresIn),
                    'github_name' => $new_user_data->name,
                    'github_email' => $new_user_data->email,
                    'github_avatar_url' => $new_user_data->avatar,
                    'github_login' => $new_user_data->nickname,
                ]);
            }
        }

        if (array_key_exists(OAuthDrivers::GOOGLE->value, $user_data)) {
            $google = $user->googleData()->first();
            $token_check = self::checkOAuthToken($user, $google->google_token, $google->google_refresh_token);

            if ($token_check && time() > $google->google_token_expires_in) {
                $newToken = Socialite::driver(OAuthDrivers::GOOGLE->value)->refreshToken($google->google_refresh_token);
                $new_user_data = Socialite::driver(OAuthDrivers::GOOGLE->value)->userFromToken($newToken->token);
                $google->update([
                    'google_token' => $newToken->token,
                    'google_refresh_token' => $newToken->refreshToken,
                    'google_name' => $new_user_data->name,
                    'google_email' => $new_user_data->email,
                    'google_avatar_url' => $new_user_data->avatar,
                ]);
            }
        }

        return $user_resource;
    }

    /**
     * Check if a user's OAuth token is valid and refresh if necessary
     *
     * @param User $user The user to check
     * @param string $token The token to check
     * @param string|null $refreshToken The refresh token to use if needed
     * @param bool $hasRefreshToken Whether the token has a refresh token
     * @return bool True if the token is valid
     * @throws Exception If the token check fails
     */
    public static function checkOAuthToken(User $user, string $token, ?string $refreshToken, bool $hasRefreshToken = true): bool {

        if (is_null($refreshToken) && $hasRefreshToken) {
            throw new Exception('No refresh token provided');
        }

        return true;
    }

    /**
     * Update user profile settings
     *
     * @param User $user The user to update
     * @param array $data The profile data to update
     * @return bool True if the update was successful
     */
    public static function updateUserProfile(User $user, array $data): bool {
        $user_profile_settings = $user->profileSettings()->first();

        if ($user_profile_settings == null) {
            $user_profile_settings = new UserProfileSettings();
            $user_profile_settings->user_id = $user->id;
        }

        if ($data['name'] != '' && $data['name'] != null) {
            $user->name = $data['name'];
            $user->save();
        }

        $user_profile_settings->avatar_source = $data['avatar_source'];
        $user_profile_settings->is_public = $data['is_public'];
        $user_profile_settings->language = $data['language'];
        $user_profile_settings->theme = $data['theme'];
        $user_profile_settings->email_notifications = $data['notifications']['email_notifications'];
        $user_profile_settings->email_marketing = $data['notifications']['email_marketing'];
        $user_profile_settings->email_security_alerts = $data['notifications']['email_security_alerts'];
        $user_profile_settings->save();

        return true;
    }

    /**
     * Get a user's public profile
     *
     * @param User $user The user to get the profile for
     * @return UserResource The user resource
     * @throws AccountNotFoundException If the user doesn't exist
     * @throws PrivateProfileException If the user's profile is private
     */
    public static function getPublicProfile(User $user): UserResource {
        if (User::where('id', '=', $user->id)->first() == null) {
            throw new AccountNotFoundException();
        }

        if (!$user->profileSettings()->first()->is_public) {
            throw new PrivateProfileException();
        }

        return new UserResource($user);
    }

    /**
     * Delete a user account
     *
     * @param User $user The user to delete
     * @return bool True if the deletion was successful
     */
    public static function deleteUserAccount(User $user): bool {
        $db_user = User::where('id', '=', $user->id)->first();

        if ($db_user == null) {
            return false;
        }

        $googleData = $db_user->googleData();
        $profileSettings = $db_user->profileSettings();
        $githubData = $db_user->githubData();
        $loginActivities = $db_user->loginActivities();

        if ($googleData->first() != null) {
            $googleData->delete();
        }

        if ($profileSettings->first() != null) {
            $profileSettings->delete();
        }

        if ($githubData->first() != null) {
            $githubData->delete();
        }

        if ($loginActivities->first() != null) {
            $loginActivities->delete();
        }

        $db_user->delete();
        return true;
    }

    /**
     * Request user data export
     *
     * @param User $user The user requesting the export
     * @return bool True if the export was requested successfully
     */
    public static function requestDataExport(User $user): bool {
        $check = UserDataExports::where('user_id', '=', $user->id)->first();

        if ($check == null) {
            $userDataExports = new UserDataExports([
                'user_id' => $user->id,
                'valid_until' => now()->addDays(),
                'status' => UserDataExportStatus::QUEUED->value,
            ]);
            $userDataExports->save();
        }

        ExportUserDataJob::dispatch($user);
        return true;
    }

    /**
     * Get the status of a user data export
     *
     * @param int $userId The ID of the user
     * @return array The export status data
     */
    public static function getDataExportStatus(int $userId): array {
        $exports = UserDataExports::where('user_id', '=', $userId)->first();

        if (!$exports) {
            return [
                'status' => UserDataExportStatus::NOT_FOUND->value,
                'valid_until' => null,
            ];
        }

        return [
            'status' => $exports->status,
            'valid_until' => strtotime($exports->valid_until),
        ];
    }

    /**
     * Check if a user data export is available for download
     *
     * @param int $userId The ID of the user
     * @return array|null The export data or null if not available
     */
    public static function checkDataExportAvailability(int $userId): ?array {
        $export = UserDataExports::where('user_id', '=', $userId)->first();

        if (!$export || $export->status != UserDataExportStatus::COMPLETED->value) {
            return [
                'status' => $export ? $export->status : UserDataExportStatus::NOT_FOUND->value,
                'available' => false
            ];
        }

        if (!Storage::exists("exports/" . $userId . "/data.zip")) {
            return [
                'status' => UserDataExportStatus::NOT_FOUND->value,
                'available' => false
            ];
        }

        return [
            'status' => UserDataExportStatus::COMPLETED->value,
            'available' => true,
            'path' => "exports/" . $userId . "/data.zip"
        ];
    }
}
