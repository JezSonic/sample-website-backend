<?php

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\TokenRefresh;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\PersonalAccessToken;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TokenRefreshTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function middleware_refreshes_token_when_access_token_is_about_to_expire()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an access token that expires in 2 minutes (should trigger refresh)
        $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes(2));

        // Create a valid refresh token
        $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays(30));

        // Make an authenticated request
        $response = $this->actingAs($user, 'sanctum')
            ->withToken($accessToken->plainTextToken)
            ->getJson('/api/user');

        $response->assertStatus(200);

        // Check if new token was provided in headers
        $this->assertTrue($response->headers->has('X-New-Access-Token'));
        $this->assertTrue($response->headers->has('X-Token-Refreshed'));
        $this->assertEquals('true', $response->headers->get('X-Token-Refreshed'));

        // Verify the new token is valid
        $newToken = $response->headers->get('X-New-Access-Token');
        $this->assertNotEmpty($newToken);
        $this->assertNotEquals($accessToken->plainTextToken, $newToken);
    }

    #[Test]
    public function middleware_does_not_refresh_token_when_access_token_is_not_expiring_soon()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an access token that expires in 30 minutes (should NOT trigger refresh)
        $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes(30));

        // Create a valid refresh token
        $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays(30));

        // Make an authenticated request
        $response = $this->actingAs($user, 'sanctum')
            ->withToken($accessToken->plainTextToken)
            ->getJson('/api/user');

        $response->assertStatus(200);

        // Check that no new token was provided
        $this->assertFalse($response->headers->has('X-New-Access-Token'));
        $this->assertFalse($response->headers->has('X-Token-Refreshed'));
    }

    #[Test]
    public function middleware_handles_missing_refresh_token_gracefully()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an access token that expires in 2 minutes (should trigger refresh attempt)
        $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes(2));

        // Don't create a refresh token

        // Make an authenticated request
        $response = $this->actingAs($user, 'sanctum')
            ->withToken($accessToken->plainTextToken)
            ->getJson('/api/user');

        $response->assertStatus(200);

        // Check that no new token was provided (graceful failure)
        $this->assertFalse($response->headers->has('X-New-Access-Token'));
        $this->assertFalse($response->headers->has('X-Token-Refreshed'));
    }

    #[Test]
    public function middleware_handles_expired_refresh_token_gracefully()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an access token that expires in 2 minutes (should trigger refresh attempt)
        $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes(2));

        // Create an expired refresh token
        $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->subDay());

        // Make an authenticated request
        $response = $this->actingAs($user, 'sanctum')
            ->withToken($accessToken->plainTextToken)
            ->getJson('/api/user');

        $response->assertStatus(200);

        // Check that no new token was provided (graceful failure)
        $this->assertFalse($response->headers->has('X-New-Access-Token'));
        $this->assertFalse($response->headers->has('X-Token-Refreshed'));
    }

    #[Test]
    public function middleware_does_not_process_unauthenticated_requests()
    {
        // Make an unauthenticated request
        $response = $this->getJson('/api/auth/login');

        $response->assertStatus(422); // Validation error for missing credentials

        // Check that no token headers are present
        $this->assertFalse($response->headers->has('X-New-Access-Token'));
        $this->assertFalse($response->headers->has('X-Token-Refreshed'));
    }

    #[Test]
    public function middleware_deletes_old_access_tokens_when_refreshing()
    {
        // Create a user
        $user = User::factory()->create();

        // Create multiple access tokens
        $accessToken1 = $user->createToken('access_token', ['*'], now()->addMinutes(2));
        $accessToken2 = $user->createToken('access_token', ['*'], now()->addMinutes(2));

        // Create a valid refresh token
        $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays(30));

        // Make an authenticated request with one of the tokens
        $response = $this->actingAs($user, 'sanctum')
            ->withToken($accessToken1->plainTextToken)
            ->getJson('/api/user');

        $response->assertStatus(200);

        // Check if new token was provided
        $this->assertTrue($response->headers->has('X-New-Access-Token'));

        // Verify old access tokens are deleted
        $this->assertNull(PersonalAccessToken::findToken($accessToken1->plainTextToken));
        $this->assertNull(PersonalAccessToken::findToken($accessToken2->plainTextToken));

        // Verify refresh token still exists
        $this->assertNotNull(PersonalAccessToken::findToken($refreshToken->plainTextToken));
    }

    #[Test]
    public function middleware_updates_refresh_token_last_used_time()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an access token that expires in 2 minutes
        $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes(2));

        // Create a valid refresh token
        $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays(30));
        $originalLastUsed = $refreshToken->accessToken->last_used_at;

        // Wait a moment to ensure timestamp difference
        sleep(1);

        // Make an authenticated request
        $response = $this->actingAs($user, 'sanctum')
            ->withToken($accessToken->plainTextToken)
            ->getJson('/api/user');

        $response->assertStatus(200);

        // Check if token was refreshed
        $this->assertTrue($response->headers->has('X-New-Access-Token'));

        // Verify refresh token last_used_at was updated
        $updatedRefreshToken = PersonalAccessToken::findToken($refreshToken->plainTextToken);
        $this->assertNotNull($updatedRefreshToken);
        $this->assertNotEquals($originalLastUsed, $updatedRefreshToken->last_used_at);
    }
}
