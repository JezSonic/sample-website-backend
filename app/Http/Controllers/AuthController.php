<?php

namespace App\Http\Controllers;

use App\Exceptions\Auth\OAuth\InvalidRefreshTokenException;
use App\Exceptions\Auth\OAuth\InvalidTokenException;
use App\Exceptions\Auth\OAuth\OAuthAccountPasswordLoginException;
use App\Exceptions\Auth\OAuth\UnsupportedDriver;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\OAuthCallbackRequest;
use App\Http\Requests\OAuthRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyPasswordResetTokenRequest;
use App\Models\User;
use App\Utils\Enums\OAuthDrivers;
use App\Utils\Services\AuthService;
use App\Utils\Services\OAuthService;
use App\Utils\Services\PasswordResetService;
use App\Utils\Services\TokenService;
use App\Utils\Traits\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Random\RandomException;

class AuthController extends Controller {
    use Response;

    /**
     * Register a new user
     *
     * @param RegisterRequest $request The registration request
     * @return JsonResponse Response indicating success
     */
    function register(RegisterRequest $request): JsonResponse {
        $data = $request->all();
        AuthService::register($data['email'], $data['password'], $data['name']);
        return $this->boolResponse(true);
    }

    /**
     * Process OAuth callback
     *
     * @param OAuthCallbackRequest $request The callback request
     * @param OAuthDrivers $driver The OAuth driver
     * @return JsonResponse Response with tokens
     * @throws UnsupportedDriver If the driver is not supported
     */
    function callback(OAuthCallbackRequest $request, OAuthDrivers $driver): JsonResponse {
        $request->session()->regenerate(true);

        // Process the OAuth callback
        $userData = OAuthService::processOAuthCallback($driver, $request);

        // Generate tokens and login
        $loginData = AuthService::processOAuthLogin(
            $userData,
            $request,
            'oauth_' . $driver->value
        );

        return $this->authResponse($loginData['id'], $loginData['access_token'], $loginData['refresh_token']);
    }

    /**
     * Login a user with email and password
     *
     * @param LoginRequest $request The login request
     * @return JsonResponse Response with tokens or error
     * @throws OAuthAccountPasswordLoginException If the account was created using OAuth
     */
    function login(LoginRequest $request): JsonResponse {
        $data = $request->all();
        $loginResult = AuthService::login($data['email'], $data['password'], $request);

        if ($loginResult === null) {
            return $this->invalidCredentialsResponse();
        }

        return $this->authResponse($loginResult['id'], $loginResult['access_token'], $loginResult['refresh_token']);
    }

    /**
     * Get OAuth redirect URL
     *
     * @param OAuthRequest $request The OAuth request
     * @param OAuthDrivers $driver The OAuth driver
     * @return JsonResponse Response with OAuth redirect URL
     * @throws UnsupportedDriver If the driver is not supported
     */
    function oauth(OAuthRequest $request, OAuthDrivers $driver): JsonResponse {
        $integrationId = $request->input('integration_id', '');
        $redirectUrl = OAuthService::getOAuthRedirectUrl($driver, $integrationId);

        return response()->json([
            /**
             * Target URL for OAuth login
             */
            'content' => $redirectUrl
        ]);
    }

    /**
     * Logout the authenticated user
     *
     * @param Request $request The request object
     * @return JsonResponse Response indicating success
     */
    function logout(Request $request): JsonResponse {
        AuthService::logout($request);
        return $this->boolResponse(true)->withoutCookie('newdev_token');
    }

    /**
     * Refresh access token using refresh token
     *
     * @param Request $request The request object
     * @return JsonResponse Response with new access token
     * @throws InvalidRefreshTokenException If the refresh token is invalid or expired
     */
    function refreshToken(Request $request): JsonResponse {
        $request->validate([
            'refresh_token' => 'required|string'
        ]);

        $refreshToken = $request->input('refresh_token');
        $tokenData = TokenService::refreshAccessToken($refreshToken);

        return response()->json($tokenData);
    }

    /**
     * Revoke a specific refresh token
     *
     * @param Request $request The request object
     * @return JsonResponse Response indicating success
     * @throws InvalidRefreshTokenException If the refresh token is invalid
     */
    function revokeRefreshToken(Request $request): JsonResponse {
        $request->validate([
            'refresh_token' => 'required|string'
        ]);

        $refreshToken = $request->input('refresh_token');
        TokenService::revokeRefreshToken($refreshToken);

        return $this->boolResponse(true)->withoutCookie('newdev_token');
    }

    /**
     * Revoke OAuth access for the authenticated user
     *
     * @param Request $request The request object
     * @param OAuthDrivers $driver The OAuth driver
     * @return JsonResponse Response indicating success
     * @throws UnsupportedDriver If the driver is not supported
     */
    function revokeOAuth(Request $request, OAuthDrivers $driver): JsonResponse {
        $user = User::find($request->user()->id);
        OAuthService::revokeOAuth($driver, $user);
        return $this->boolResponse(true);
    }

    /**
     * Request a password reset
     *
     * @param ChangePasswordRequest $request The password reset request
     * @return JsonResponse Response indicating success
     * @throws RandomException
     */
    public function requestChangePassword(ChangePasswordRequest $request): JsonResponse {
        $email = $request->input('email');
        PasswordResetService::requestPasswordReset($email);
        return $this->boolResponse(true);
    }

    /**
     * Change password using a reset token
     *
     * @param Request $request The request object
     * @return JsonResponse Response indicating success
     * @throws InvalidTokenException If the token is invalid or expired
     */
    public function changePassword(Request $request): JsonResponse {
        $data = $request->all();
        PasswordResetService::changePassword($data['token'], $data['password']);
        return $this->boolResponse(true);
    }

    /**
     * Verify a password reset token
     *
     * @param VerifyPasswordResetTokenRequest $request The token verification request
     * @return JsonResponse Response with token validity information
     * @throws InvalidTokenException If the token is invalid
     */
    public function verifyPasswordResetToken(VerifyPasswordResetTokenRequest $request): JsonResponse {
        $token = $request->input('token');
        $result = PasswordResetService::verifyToken($token);
        return response()->json($result);
    }
}
