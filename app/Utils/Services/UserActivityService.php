<?php

namespace App\Utils\Services;

use App\Http\Resources\UserLoginActivityResource;
use App\Models\UserLoginActivity;
use Illuminate\Support\Facades\Auth;

class UserActivityService {
    /**
     * Log a user login activity
     *
     * @param int $userId The ID of the user
     * @param string|null $ipAddress The IP address of the user
     * @param string|null $userAgent The user agent string
     * @param string $loginMethod The method used for login (email, oauth_github, oauth_google, etc.)
     * @return UserLoginActivity The created login activity record
     */
    public static function logLoginActivity(
        int     $userId,
        ?string $ipAddress,
        ?string $userAgent,
        string  $loginMethod
    ): UserLoginActivity {
        $location = IpLocationService::getLocationFromIp($ipAddress);

        $activity = UserLoginActivity::create([
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'location' => $location,
            'user_agent' => $userAgent,
            'login_method' => $loginMethod,
        ]);

        $activity->save();
        return $activity;
    }

    /**
     * Get login activity for a user
     *
     * @param int|null $userId The ID of the user (defaults to authenticated user)
     * @return array Collection of login activity resources
     */
    public static function getLoginActivity(?int $userId = null): array {
        $userId = $userId ?? Auth::id();

        if (!$userId) {
            return [];
        }

        $data = UserLoginActivity::where('user_id', '=', $userId)->get();
        return UserLoginActivityResource::collection($data)->toArray(request());
    }
}
