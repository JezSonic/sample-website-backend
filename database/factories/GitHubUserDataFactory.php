<?php

namespace Database\Factories;

use App\Models\GitHubUserData;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GitHubUserData>
 */
class GitHubUserDataFactory extends Factory {
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GitHubUserData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'user_id' => User::factory(),
            'id' => fake()->uuid(),
            'github_login' => fake()->userName(),
            'github_avatar_url' => fake()->imageUrl(),
            'github_gravatar_id' => fake()->uuid(),
            'github_url' => fake()->url(),
            'github_html_url' => fake()->url(),
            'github_followers_url' => fake()->url(),
            'github_following_url' => fake()->url(),
            'github_gists_url' => fake()->url(),
            'github_starred_url' => fake()->url(),
            'github_subscriptions_url' => fake()->url(),
            'github_organizations_url' => fake()->url(),
            'github_repos_url' => fake()->url(),
            'github_events_url' => fake()->url(),
            'github_received_events_url' => fake()->url(),
            'github_type' => 'User',
            'github_user_view_type' => 'User',
            'github_site_admin' => fake()->boolean(),
            'github_name' => fake()->name(),
            'github_company' => fake()->company(),
            'github_blog' => fake()->url(),
            'github_location' => fake()->city(),
            'github_email' => fake()->email(),
            'github_hireable' => fake()->boolean(),
            'github_bio' => fake()->paragraph(),
            'github_twitter_username' => fake()->userName(),
            'github_notification_email' => fake()->email(),
            'public_repos' => fake()->numberBetween(0, 100),
            'public_gists' => fake()->numberBetween(0, 50),
            'public_followers' => fake()->numberBetween(0, 1000),
            'public_following' => fake()->numberBetween(0, 500),
            'github_token' => fake()->sha256(),
            'github_refresh_token' => fake()->sha256(),
        ];
    }
}
