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
     * Get login activity for a user with pagination
     *
     * @param int|null $userId The ID of the user (defaults to authenticated user)
     * @param int $page The page number (defaults to 1)
     * @param int $perPage Number of items per page (defaults to 10)
     * @return array Paginated collection of login activity resources with metadata
     */
    public static function getLoginActivity(?int $userId = null, int $page = 1, int $perPage = 10): array {
        $userId = $userId ?? Auth::id();

        if (!$userId) {
            return [
                'data' => [],
                'total' => 0,
                'current_page' => $page,
                'per_page' => $perPage,
                'total_pages' => 0
            ];
        }

        $query = UserLoginActivity::where('user_id', '=', $userId);
        $total = $query->count();
        $totalPages = ceil($total / $perPage);

        $data = $query->orderBy('created_at')
                      ->skip(($page - 1) * $perPage)
                      ->take($perPage)
                      ->get();

        return [
            'data' => UserLoginActivityResource::collection($data)->toArray(request()),
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages
        ];
    }
}
