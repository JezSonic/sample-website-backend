<?php

namespace App\Http\Controllers;

use App\Exceptions\Auth\OAuth\InvalidTokenException;
use App\Exceptions\User\AccountNotFoundException;
use App\Exceptions\User\PrivateProfileException;
use App\Http\Requests\NotificationsUpdateRequest;
use App\Http\Requests\ProfileSettingsUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Utils\Enums\UserDataExportStatus;
use App\Utils\Services\EmailVerificationService;
use App\Utils\Services\UserService;
use App\Utils\Traits\Response;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller {
    use Response;

    /**
     * Get the authenticated user's profile
     *
     * @param Request $request The request object
     * @return UserResource The user resource
     * @throws Exception If token validation fails
     */
    public function index(Request $request): UserResource {
        return UserService::getUserProfile($request->user());
    }

    /**
     * Update the authenticated user's profile settings
     *
     * @param ProfileSettingsUpdateRequest $request The profile update request
     * @return JsonResponse Response indicating success
     */
    public function update(ProfileSettingsUpdateRequest $request): JsonResponse {
        $data = $request->all();
        $user = User::where('id', '=', $request->user()->id)->first();
        UserService::updateUserProfile($user, $data);
        return $this->boolResponse(true);
    }

    public function updateNotifications(NotificationsUpdateRequest $request): JsonResponse {
        $data = $request->all();
        $user = User::where('id', '=', $request->user()->id)->first();
        $user->profileSettings()->update($data);
        return $this->boolResponse(true);
    }

    /**
     * Get a user's public profile
     *
     * @param User $user The user to get the profile for
     * @return UserResource The user resource
     * @throws PrivateProfileException If the user's profile is private
     * @throws AccountNotFoundException If the user doesn't exist
     */
    public function show(User $user): UserResource {
        return UserService::getPublicProfile($user);
    }

    /**
     * Delete the authenticated user's account
     *
     * @param Request $request The request object
     * @return JsonResponse Response indicating success
     */
    public function destroy(Request $request): JsonResponse {
        $user = User::where('id', '=', $request->user()->id)->first();

        if (!UserService::deleteUserAccount($user)) {
            return $this->invalidCredentialsResponse();
        }

        return $this->boolResponse(true);
    }

    /**
     * Send a verification email to the authenticated user
     *
     * @return JsonResponse Response indicating success
     */
    public function sendVerificationEmail(Request $request): JsonResponse {
        $user = User::where('id', '=', $request->user()->id)->first();

        if ($user == null) {
            return $this->invalidCredentialsResponse();
        }

        EmailVerificationService::sendVerificationEmail($user);
        return $this->boolResponse(true);
    }

    /**
     * Verify a user's email using a verification token
     *
     * @param string $token The verification token
     * @return JsonResponse Response indicating success
     * @throws InvalidTokenException If the token is invalid or expired
     */
    public function verifyEmail(string $token): JsonResponse {
        EmailVerificationService::verifyEmail($token);
        return $this->boolResponse(true);
    }

    /**
     * Request a data export for the authenticated user
     *
     * @param Request $request The request object
     * @return JsonResponse Response indicating success
     */
    public function exportUserData(Request $request): JsonResponse {
        $user = User::where('id', '=', $request->user()->id)->first();
        UserService::requestDataExport($user);
        return $this->boolResponse(true);
    }


    /**
     * Download exported data for a user
     *
     * @param int $userId The ID of the user
     * @return JsonResponse|StreamedResponse Response with export status or the exported data
     */
    public function downloadExportedData(int $userId): JsonResponse|StreamedResponse {
        $exportData = UserService::checkDataExportAvailability($userId);

        if (!$exportData['available']) {
            return response()->json([
                'status' => $exportData['status']
            ], $exportData['status'] === UserDataExportStatus::NOT_FOUND->value ? 404 : 200);
        }

        return Storage::download($exportData['path'], "data.zip");
    }

    /**
     * Check the status of a user data export
     *
     * @param int $userId The ID of the user
     * @return JsonResponse Response with export status information
     */
    public function checkExportDataStatus(int $userId): JsonResponse {
        $statusData = UserService::getDataExportStatus($userId);
        return response()->json($statusData);
    }
}
