<?php

namespace App\Utils\Services;

use App\Exceptions\User\AccountNotFoundException;
use App\Exceptions\User\PrivateProfileException;
use App\Http\Resources\UserResource;
use App\Jobs\ExportUserDataJob;
use App\Models\User;
use App\Models\UserDataExports;
use App\Models\UserProfileSettings;
use App\Utils\Enums\UserDataExportStatus;
use App\Exceptions\Auth\OAuth\NoRefreshTokenException;
use Exception;
use Illuminate\Support\Facades\Storage;

class UserService {
    /**
     * Get user profile data
     *
     * @param User $user The user to get profile data for
     * @return UserResource The user resource
     * @throws Exception If token validation fails
     */
    public static function getUserProfile(User $user, bool $forEditing = false): UserResource {
        if (!$user->profileSettings()->first()->is_public && !$forEditing) {
            throw new PrivateProfileException();
        }

        return new UserResource($user);
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
                /**
                 * Status of the data export request
                 * @var UserDataExportStatus
                 */
                'status' => UserDataExportStatus::NOT_FOUND->value,

                /**
                 * The date until the exported data is available, null if not available
                 * @var string|null
                 */
                'valid_until' => null,
            ];
        }

        return [
            /**
             * Status of the data export request
             * @var UserDataExportStatus
             */
            'status' => $exports->status,

            /**
             * The date until the exported data is available, null if not available
             * @var string|null
             */
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
