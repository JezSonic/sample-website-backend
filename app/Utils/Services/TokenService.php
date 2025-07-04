<?php

namespace App\Utils\Services;

use App\Exceptions\User\InvalidRefreshTokenException;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class TokenService {
    /**
     * Create an access token for a user
     *
     * @param mixed $user The user to create the token for
     * @param int $expiresInSeconds Token expiration time in seconds
     * @return string The plain text access token
     */
    public static function createAccessToken(mixed $user, int $expiresInSeconds = 3600): string {
        $accessToken = $user->createToken('access_token', ['*'], now()->addSeconds($expiresInSeconds));
        return $accessToken->plainTextToken;
    }

    /**
     * Create a refresh token for a user
     *
     * @param mixed $user The user to create the token for
     * @param int $expiresInDays Token expiration time in days
     * @return string The plain text refresh token
     */
    public static function createRefreshToken(mixed $user, int $expiresInDays = 30): string {
        $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays($expiresInDays));
        return $refreshToken->plainTextToken;
    }

    /**
     * Refresh an access token using a refresh token
     *
     * @param string $refreshToken The refresh token to use
     * @return array The new access token data
     * @throws InvalidRefreshTokenException If the refresh token is invalid or expired
     */
    public static function refreshAccessToken(string $refreshToken): array {
        // Find the refresh token in the database
        $tokenModel = PersonalAccessToken::findToken($refreshToken);

        if (!$tokenModel || $tokenModel->name !== 'refresh_token') {
            throw new InvalidRefreshTokenException();
        }

        // Check if refresh token is expired
        if ($tokenModel->expires_at && $tokenModel->expires_at->isPast()) {
            $tokenModel->delete();
            throw new InvalidRefreshTokenException();
        }

        // Check if refresh token has the correct ability
        if (!$tokenModel->can('refresh')) {
            throw new InvalidRefreshTokenException();
        }

        $user = $tokenModel->tokenable;

        // Delete old access tokens (keep refresh tokens)
        $user->tokens()->where('name', 'access_token')->delete();

        $accessToken = $user->createToken('access_token', ['*'], now()->addHour());
        $tokenModel->forceFill(['last_used_at' => now()])->save();

        return [
            'access_token' => $accessToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600 // 1 hour in seconds
        ];
    }

    /**
     * Revoke a refresh token
     *
     * @param string $refreshToken The refresh token to revoke
     * @return bool True if the token was revoked successfully
     * @throws InvalidRefreshTokenException If the refresh token is invalid
     */
    public static function revokeRefreshToken(string $refreshToken): bool {
        // Find the refresh token in the database
        $tokenModel = PersonalAccessToken::findToken($refreshToken);

        if (!$tokenModel || $tokenModel->name !== 'refresh_token') {
            throw new InvalidRefreshTokenException();
        }

        // Delete the refresh token
        $tokenModel->delete();
        return true;
    }

    /**
     * Revoke all tokens for the authenticated user
     *
     * @return bool True if the tokens were revoked successfully
     */
    public static function revokeAllTokens(): bool {
        Auth::user()?->tokens()->delete();
        return true;
    }
}
