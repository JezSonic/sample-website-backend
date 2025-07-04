<?php

namespace App\Http\Controllers;

use App\Utils\Services\UserActivityService;
use App\Utils\Traits\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserLoginActivityController extends Controller {
    use Response;

    /**
     * Get login activity for the authenticated user
     *
     * @param Request $request The request object
     * @return JsonResponse Response with login activity data
     */
    public function index(Request $request): JsonResponse {
        $data = UserActivityService::getLoginActivity();
        return response()->json([
            'content' => $data
        ]);
    }
}
