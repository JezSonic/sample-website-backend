<?php

namespace Tests\Feature\Database;

use App\Models\GitHubUserData;
use App\Models\GoogleUserData;
use App\Models\User;
use App\Models\UserLoginActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeedersTest extends TestCase {
    use RefreshDatabase;

    #[Test]
    public function database_seeder_creates_test_user_with_all_related_models() {
        // Run the database seeder
        $this->seed();

        // Check that the test user exists
        $testUser = User::where('email', '=', 'test@example.com')->first();
        $this->assertNotNull($testUser);
        $this->assertEquals('Test User', $testUser->name);

        // Check that the test user has GitHub data
        $this->assertNotNull($testUser->gitHubData);
        $this->assertEquals($testUser->name, $testUser->gitHubData->github_name);
        $this->assertEquals($testUser->email, $testUser->gitHubData->github_email);

        // Check that the test user has Google data
        $this->assertNotNull($testUser->googleData);
        $this->assertEquals($testUser->name, $testUser->googleData->google_name);
        $this->assertEquals($testUser->email, $testUser->googleData->google_email);

        // Check that the test user has profile settings
        $this->assertNotNull($testUser->profileSettings);

        // Check that the test user has login activities
        $this->assertCount(3, $testUser->loginActivities);
    }

    #[Test]
    public function database_seeder_creates_additional_users_with_related_models() {
        // Run the database seeder
        $this->seed();

        // Check that there are additional users
        $this->assertTrue(User::count() > 1);

        // Get all users except the test user
        $users = User::where('email', '!=', 'test@example.com')->get();
        $this->assertCount(5, $users);

        // Check that each user has profile settings
        foreach ($users as $user) {
            $this->assertNotNull($user->profileSettings);
        }

        // Check that some users have GitHub data
        $this->assertTrue(GitHubUserData::count() > 1);

        // Check that some users have Google data
        $this->assertTrue(GoogleUserData::count() > 1);

        // Check that some users have login activities
        $this->assertTrue(UserLoginActivity::count() > 3);
    }
}
