<?php

namespace Tests\Unit\Models;

use App\Models\GitHubUserData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GitHubUserDataTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $githubData = new GitHubUserData();
        $this->assertEquals([
            'user_id',
            'id',
            'github_login',
            'github_avatar_url',
            'github_gravatar_id',
            'github_url',
            'github_html_url',
            'github_followers_url',
            'github_following_url',
            'github_gists_url',
            'github_starred_url',
            'github_subscriptions_url',
            'github_organizations_url',
            'github_repos_url',
            'github_events_url',
            'github_received_events_url',
            'github_type',
            'github_user_view_type',
            'github_site_admin',
            'github_name',
            'github_company',
            'github_blog',
            'github_location',
            'github_email',
            'github_hireable',
            'github_bio',
            'github_twitter_username',
            'github_notification_email',
            'public_repos',
            'public_gists',
            'public_followers',
            'public_following',
            'github_token',
            'github_token_expires_in',
            'github_refresh_token'
        ], $githubData->getFillable());
    }

    /** @test */
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $githubData = GitHubUserData::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertInstanceOf(User::class, $githubData->user);
        $this->assertEquals($user->id, $githubData->user->id);
    }

    /** @test */
    public function it_has_no_timestamps()
    {
        $githubData = new GitHubUserData();
        $this->assertFalse($githubData->timestamps);
    }

    /** @test */
    public function it_has_correct_table_name()
    {
        $githubData = new GitHubUserData();
        $this->assertEquals('github_user_data', $githubData->getTable());
    }
}
