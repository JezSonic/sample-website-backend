<?php

namespace Tests\Feature\Models;

use App\Models\GitHubUserData;
use App\Models\GoogleUserData;
use App\Models\User;
use App\Models\UserLoginActivity;
use App\Models\UserProfileSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserWithRelationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_user_with_all_related_models()
    {
        // Create a user
        $user = User::factory()->create();

        // Create related models
        GitHubUserData::factory()->create([
            'user_id' => $user->id,
            'github_name' => $user->name,
            'github_email' => $user->email,
        ]);

        GoogleUserData::factory()->create([
            'user_id' => $user->id,
            'google_name' => $user->name,
            'google_email' => $user->email,
        ]);

        UserProfileSettings::factory()->create([
            'user_id' => $user->id,
            'theme' => 'dark',
            'language' => 'en',
        ]);

        UserLoginActivity::factory(3)->create([
            'user_id' => $user->id,
        ]);

        // Refresh the user model to load relationships
        $user = $user->fresh();

        // Assert relationships exist
        $this->assertNotNull($user->gitHubData);
        $this->assertEquals($user->name, $user->gitHubData->github_name);
        $this->assertEquals($user->email, $user->gitHubData->github_email);

        $this->assertNotNull($user->googleData);
        $this->assertEquals($user->name, $user->googleData->google_name);
        $this->assertEquals($user->email, $user->googleData->google_email);

        $this->assertNotNull($user->profileSettings);
        $this->assertEquals('dark', $user->profileSettings->theme);
        $this->assertEquals('en', $user->profileSettings->language);

        $this->assertCount(3, $user->loginActivities);
    }

    /** @test */
    public function can_create_user_with_only_some_related_models()
    {
        // Create a user
        $user = User::factory()->create();

        // Create only GitHub data and profile settings
        GitHubUserData::factory()->create([
            'user_id' => $user->id,
        ]);

        UserProfileSettings::factory()->create([
            'user_id' => $user->id,
        ]);

        // Refresh the user model to load relationships
        $user = $user->fresh();

        // Assert relationships exist
        $this->assertNotNull($user->gitHubData);
        $this->assertNull($user->googleData);
        $this->assertNotNull($user->profileSettings);
        $this->assertCount(0, $user->loginActivities);
    }
}
