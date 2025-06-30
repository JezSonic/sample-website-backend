<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserLoginActivityResource;
use App\Models\UserLoginActivity;
use App\Utils\Traits\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLoginActivityController extends Controller {
    use Response;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse {
        $data = UserLoginActivity::where('user_id', '=', Auth::user()->id)->get();
        return response()->json([
            'content' => UserLoginActivityResource::collection($data)
        ]);
    }
}
