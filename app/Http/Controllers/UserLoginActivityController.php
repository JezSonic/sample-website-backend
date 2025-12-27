<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserActivityRequest;
use App\Utils\Services\UserActivityService;
use App\Utils\Traits\Response;
use Illuminate\Http\JsonResponse;

class UserLoginActivityController extends Controller {
    use Response;

    /**
     * Get login activity for the authenticated user with pagination
     *
     * @param UserActivityRequest $request The request object
     * @return JsonResponse Response with paginated login activity data
     */
    public function index(UserActivityRequest $request): JsonResponse {
        $body = $request->all();
        $data = UserActivityService::getLoginActivity($request->user()->id, $body['page'], $body['per_page']);

        return $this->paginatedResponse($data['data'], $data['total'], $body['page'], $body['per_page'], $data['total_pages']);
    }
}
