<?php

namespace Database\Seeders;

use App\Models\GitHubUserData;
use App\Models\GoogleUserData;
use App\Models\User;
use App\Models\UserLoginActivity;
use App\Models\UserProfileSettings;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {
        // Create a test user with all related models
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

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
        ]);

        UserLoginActivity::factory(3)->create([
            'user_id' => $user->id,
        ]);

        // Create additional random users with their related models
        User::factory(5)->create()->each(function ($user) {
            // 50% chance to have GitHub data
            if (rand(0, 1)) {
                GitHubUserData::factory()->create([
                    'user_id' => $user->id,
                ]);
            }

            // 50% chance to have Google data
            if (rand(0, 1)) {
                GoogleUserData::factory()->create([
                    'user_id' => $user->id,
                ]);
            }

            // All users have profile settings
            UserProfileSettings::factory()->create([
                'user_id' => $user->id,
            ]);

            // Random number of login activities (0-5)
            $loginCount = rand(0, 5);
            if ($loginCount > 0) {
                UserLoginActivity::factory($loginCount)->create([
                    'user_id' => $user->id,
                ]);
            }
        });
    }
}
