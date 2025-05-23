<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserProfileSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserProfileSettings>
 */
class UserProfileSettingsFactory extends Factory {
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserProfileSettings::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'user_id' => User::factory(),
            'theme' => fake()->randomElement(['dark']),
            'language' => fake()->randomElement(['en']),
            'timezone' => fake()->randomElement(['Europe/London']),
            'avatar_source' => fake()->randomElement(['github', 'google', null]),
            'is_public' => true,
            'email_notifications' => false,
            'email_marketing' => false,
            'email_security_alerts' => false,
        ];
    }
}
