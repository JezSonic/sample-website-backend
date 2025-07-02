<?php

namespace App\Http\Middleware;

use App\Exceptions\User\InvalidRefreshTokenException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class TokenRefresh {
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response {
        // Only process if user is authenticated via Sanctum
        if (Auth::guard('sanctum')->check()) {
            $user = $request->user();
            $currentToken = $user->tokens()->where('name', 'access_token')->firstOrFail();

            // Check if current access token is about to expire (within 5 minutes) or already expired
            if ($currentToken && $this->shouldRefreshToken($currentToken)) {
                $tokenId = explode("|", $request->headers->get('X-Refresh-Token'))[0];
                $refreshToken = $user->tokens()
                    ->where('name', 'refresh_token')
                    ->where('id', $tokenId)
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->get()[0];

                if ($refreshToken) {
                    try {
                        $newAccessToken = $this->refreshAccessToken($user, $refreshToken);

                        // Add the new token to response headers
                        $response = $next($request);
                        $response->headers->set('X-New-Access-Token', $newAccessToken);
                        $response->headers->set('X-Token-Refreshed', 'true');

                        return $response;
                    } catch (InvalidRefreshTokenException $e) {
                        // If refresh fails, continue with current token
                        // The auth:sanctum middleware will handle expired tokens
                    }
                }
            }
        }

        return $next($request);
    }

    /**
     * Check if the token should be refreshed
     */
    private function shouldRefreshToken(PersonalAccessToken $token): bool {
        // If token has no expiration, don't refresh
        if (!$token->expires_at) {
            return false;
        }

        // Refresh if token expires within 5 minutes or is already expired
        return $token->expires_at->subMinutes(5)->isPast();
    }

    /**
     * Get a valid refresh token for the user
     */
    private function getValidRefreshToken($user): ?PersonalAccessToken {
        $refreshTokens = $user->tokens()
            ->where('name', 'refresh_token')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get();

        foreach ($refreshTokens as $token) {
            if ($token->can('refresh')) {
                return $token;
            }
        }

        return null;
    }

    /**
     * Refresh the access token using refresh token
     *
     * @throws InvalidRefreshTokenException
     */
    private function refreshAccessToken($user, PersonalAccessToken $refreshToken): string {
        // Validate refresh token
        if ($refreshToken->name !== 'refresh_token') {
            throw new InvalidRefreshTokenException();
        }

        // Check if refresh token is expired
        if ($refreshToken->expires_at && $refreshToken->expires_at->isPast()) {
            $refreshToken->delete();
            throw new InvalidRefreshTokenException();
        }

        // Check if refresh token has the correct ability
        if (!$refreshToken->can('refresh')) {
            throw new InvalidRefreshTokenException();
        }

        // Delete old access tokens (keep refresh tokens)
        $user->tokens()->where('name', 'access_token')->delete();

        // Create new access token
        $accessToken = $user->createToken('access_token', ['*'], now()->addHour());

        // Update refresh token last used time
        $refreshToken->forceFill(['last_used_at' => now()])->save();

        return $accessToken->plainTextToken;
    }
}
