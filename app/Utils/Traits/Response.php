<?php

namespace App\Utils\Traits;

use Illuminate\Http\JsonResponse;

trait Response {
    function boolResponse(bool $value): JsonResponse {
        return response()->json([
            /**
             * Boolean indicating whether the operation was successful or not
             * @type bool
             * @example true
             */
            "content" => $value
        ]);
    }

    function availableResponse(bool $value): JsonResponse {
        return response()->json([
            /**
             * Boolean indicating whether the specific resource is available or not
             * @type bool
             * @example true
             */
            "content" => $value
        ]);
    }

    function invalidCredentialsResponse(): JsonResponse {
        return response()->json([
            /**
             * Message indicating that invalid login credentials such as email, password, etc. were provided.
             */
            'content' => 'invalid_credentials'
        ], 401);
    }
}
