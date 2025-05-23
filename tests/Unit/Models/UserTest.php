<?php

namespace Tests\Unit\Models;

use App\Models\GitHubUserData;
use App\Models\GoogleUserData;
use App\Models\User;
use App\Models\UserLoginActivity;
use App\Models\UserProfileSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase {
    use RefreshDatabase;

    #[Test]
    public function it_has_correct_fillable_attributes() {
        $user = new User();
        $this->assertEquals([
            'name',
            'email',
            'email_verified_at',
            'password',
            'github_id',
            'github_token',
            'github_refresh_token'
        ], $user->getFillable());
    }

    #[Test]
    public function it_has_correct_hidden_attributes() {
        $user = new User();
        $this->assertEquals([
            'password',
            'salt',
            'remember_token',
        ], $user->getHidden());
    }

    #[Test]
    public function it_can_get_salt() {
        $user = User::factory()->create([
            'salt' => 'test-salt'
        ]);

        $this->assertEquals('test-salt', $user->getSalt());
    }

    #[Test]
    public function it_has_google_data_relationship() {
        $user = User::factory()->create();
        GoogleUserData::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertInstanceOf(GoogleUserData::class, $user->googleData);
        $this->assertEquals($user->id, $user->googleData->user_id);
    }

    #[Test]
    public function it_has_github_data_relationship() {
        $user = User::factory()->create();
        GitHubUserData::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertInstanceOf(GitHubUserData::class, $user->gitHubData);
        $this->assertEquals($user->id, $user->gitHubData->user_id);
    }

    #[Test]
    public function it_has_profile_settings_relationship() {
        $user = User::factory()->create();
        UserProfileSettings::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertInstanceOf(UserProfileSettings::class, $user->profileSettings);
        $this->assertEquals($user->id, $user->profileSettings->user_id);
    }

    #[Test]
    public function it_has_login_activities_relationship() {
        $user = User::factory()->create();
        UserLoginActivity::factory(3)->create([
            'user_id' => $user->id
        ]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->loginActivities);
        $this->assertCount(3, $user->loginActivities);
        $this->assertInstanceOf(UserLoginActivity::class, $user->loginActivities->first());
    }
}
