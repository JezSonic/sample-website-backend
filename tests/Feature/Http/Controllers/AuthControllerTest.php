<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function login_returns_access_and_refresh_tokens()
    {
        // Create a user with salt for password authentication
        $salt = 'test-salt';
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123' . $salt),
            'salt' => $salt
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'content',
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in'
            ]);

        $data = $response->json();
        $this->assertEquals('Bearer', $data['token_type']);
        $this->assertEquals(3600, $data['expires_in']);
        $this->assertNotEmpty($data['access_token']);
        $this->assertNotEmpty($data['refresh_token']);
    }

    #[Test]
    public function refresh_token_generates_new_access_token()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a refresh token manually
        $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays(30));

        $response = $this->postJson('/api/auth/refresh', [
            'refresh_token' => $refreshToken->plainTextToken
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in'
            ]);

        $data = $response->json();
        $this->assertEquals('Bearer', $data['token_type']);
        $this->assertEquals(3600, $data['expires_in']);
        $this->assertNotEmpty($data['access_token']);
    }

    #[Test]
    public function refresh_token_fails_with_invalid_token()
    {
        $response = $this->postJson('/api/auth/refresh', [
            'refresh_token' => 'invalid-token'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid refresh token'
            ]);
    }

    #[Test]
    public function refresh_token_fails_with_expired_token()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an expired refresh token
        $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->subDay());

        $response = $this->postJson('/api/auth/refresh', [
            'refresh_token' => $refreshToken->plainTextToken
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Refresh token expired'
            ]);
    }

    #[Test]
    public function refresh_token_fails_with_access_token()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an access token (not refresh token)
        $accessToken = $user->createToken('access_token', ['*'], now()->addHour());

        $response = $this->postJson('/api/auth/refresh', [
            'refresh_token' => $accessToken->plainTextToken
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid refresh token'
            ]);
    }

    #[Test]
    public function revoke_refresh_token_works()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a refresh token
        $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays(30));

        $response = $this->postJson('/api/auth/revoke-refresh', [
            'refresh_token' => $refreshToken->plainTextToken
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Refresh token revoked successfully'
            ]);

        // Verify token is deleted
        $this->assertNull(PersonalAccessToken::findToken($refreshToken->plainTextToken));
    }

    #[Test]
    public function revoke_refresh_token_fails_with_invalid_token()
    {
        $response = $this->postJson('/api/auth/revoke-refresh', [
            'refresh_token' => 'invalid-token'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid refresh token'
            ]);
    }

    #[Test]
    public function logout_deletes_all_tokens()
    {
        // Create a user
        $user = User::factory()->create();

        // Create both access and refresh tokens
        $accessToken = $user->createToken('access_token', ['*'], now()->addHour());
        $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays(30));

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/auth/logout');

        $response->assertStatus(200);

        // Verify all tokens are deleted
        $this->assertNull(PersonalAccessToken::findToken($accessToken->plainTextToken));
        $this->assertNull(PersonalAccessToken::findToken($refreshToken->plainTextToken));
    }
}
