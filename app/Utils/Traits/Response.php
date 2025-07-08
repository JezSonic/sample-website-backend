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

    function paginatedResponse(array $data, int $total, int $perPage, int $page, int $totalPages): JsonResponse {
        return response()->json([
            /**
             * Data returned to the requested page
             */
            'data' => $data,

            /**
             * Total number of entries across all pages
             * @type int
             */
            'total' => $total,

            /**
             * Number of page the data is from
             * @type int
             */
            'current_page' => $page,

            /**
             * Number of items per page
             * @type int
             */
            'per_page' => $perPage,

            /**
             * Number of all pages available
             * @type int
             */
            'total_pages' => $totalPages
        ]);
    }

    function authResponse(int $userId, string $accessToken, string $refreshToken, string $tokenType  = 'Bearer', int $expiresIn = 3600): JsonResponse {
        return response()->json([

            /**
             * ID of the user the tokens are assigned to
             *
             * @type int
             */
            'id' => $userId,

            /**
             * Access token
             *
             * @type string
             */
            'access_token' => $accessToken,

            /**
             * Refresh token
             *
             * @type string
             */
            'refresh_token' => $refreshToken,

            /**
             * Token type
             *
             * @type string
             */
            'token_type' => $tokenType,

            /**
             * Time in seconds for how long the access token is valid for
             *
             * @type int
             */
            'expires_in' => $expiresIn
        ]);
    }
}
