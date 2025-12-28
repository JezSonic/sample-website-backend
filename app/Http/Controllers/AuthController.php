<?php

namespace App\Http\Controllers;

use App\Exceptions\Auth\OAuth\InvalidRefreshTokenException;
use App\Exceptions\Auth\OAuth\InvalidTokenException;
use App\Exceptions\Auth\OAuth\OAuthAccountPasswordLoginException;
use App\Exceptions\Auth\OAuth\UnsupportedDriver;
use App\Exceptions\Auth\TwoFactor\TwoFactorAlreadyEnabledException;
use App\Exceptions\Auth\TwoFactor\TwoFactorRequiredException;
use App\Exceptions\User\AccountNotFoundException;
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
     * Log in a user with email and password
     *
     * @param LoginRequest $request The login request
     * @return JsonResponse Response with tokens or error
     * @throws AccountNotFoundException If the account does not exist
     * @throws OAuthAccountPasswordLoginException|TwoFactorRequiredException If the account was created using OAuth
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
     * @return JsonResponse Response with a new access token
     * @throws InvalidRefreshTokenException If the refresh token is invalid or expired
     */
    function refreshToken(Request $request): JsonResponse {
        $request->validate([
            /** Refresh token to generate an access token from */
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
            /** Refresh token to be revoked */
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

    /**
     * Prepare 2FA Authentication (for the setup first only)
     * @throws TwoFactorAlreadyEnabledException
     */
    public function prepareTwoFactor(Request $request): JsonResponse {
        $user = $request->user();
        if ($user == null) {
            return $this->invalidCredentialsResponse();
        }
        if ($user->hasTwoFactorEnabled()) {
            throw new TwoFactorAlreadyEnabledException();
        }
        $secret = $user->createTwoFactorAuth();
        return response()->json([
            /**
             * QR Code for two-factor authentication
             */
            'qr_code' => $secret->toQr(),

            /**
             * URI for two-factor authentication
             */
            'uri' => $secret->toUri()
        ]);
    }

    /**
     * Confirm and enable 2FA Authentication (after setup first only)
     * @throws TwoFactorAlreadyEnabledException
     */
    public function confirmTwoFactor(Request $request): JsonResponse {
        $user = User::find($request->user()->id);
        $data = $request->validate([
            /**
             * Two-factor authentication code provided by the user
             */
            'code' => 'required'
        ]);

        if ($user == null) {
            return $this->invalidCredentialsResponse();
        }

        if ($user->hasTwoFactorEnabled()) {
            throw new TwoFactorAlreadyEnabledException();
        }

        // Normalize the code: keep digits only, preserve/pad leading zeros to 6 digits
        $rawCode = (string) ($data['code'] ?? '');
        $normalized = preg_replace('/\D+/', '', $rawCode) ?? '';
        if (strlen($normalized) < 6) {
            $normalized = str_pad($normalized, 6, '0', STR_PAD_LEFT);
        }

        // If after normalization it's not exactly 6 digits, reject
        if (!preg_match('/^\d{6}$/', $normalized)) {
            return $this->invalidCredentialsResponse();
        }

        // Log actual TOTP settings to debug potential mismatch
        $tfa = $user->twoFactorAuth;$activated = $user->confirmTwoFactorAuth($normalized);
        if ($activated) {
            return response()->json([
                /**
                 * Recovery codes for the user
                 * @var string[]
                 */
                'recovery_codes' => $user->getRecoveryCodes()
            ]);
        } else {
            return $this->invalidCredentialsResponse();
        }
    }

    /**
     * Generate new recovery codes for the authenticated user
     *
     * @param Request $request The request object containing user information
     * @return JsonResponse Response with the user's recovery codes
     */
    public function getRecoveryCodes(Request $request): JsonResponse {
        $user = User::find($request->user()->id);
        if ($user == null) {
            return $this->invalidCredentialsResponse();
        }
        return response()->json([
            /**
             * Recovery codes for the user
             * @var string[]
             */
            'recovery_codes' => $user->getRecoveryCodes()
        ]);
    }

    /**
     * Disable two-factor authentication for the authenticated user
     *
     * @param Request $request The request instance
     * @return JsonResponse Response indicating the operation status
     */
    public function disableTwoFactorAuth(Request $request): JsonResponse {
        $request->user()->disableTwoFactorAuth();
        return $this->boolResponse(true);
    }
}
