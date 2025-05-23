<?php

namespace Database\Factories;

use App\Models\GoogleUserData;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoogleUserData>
 */
class GoogleUserDataFactory extends Factory {
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GoogleUserData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'user_id' => User::factory(),
            'id' => fake()->uuid(),
            'google_name' => fake()->name(),
            'google_email' => fake()->email(),
            'google_avatar_url' => fake()->imageUrl(),
            'google_token' => fake()->sha256(),
            'google_refresh_token' => fake()->sha256(),
            'google_token_expires_in' => fake()->numberBetween(3000, 4000),
            'google_nickname' => fake()->userName(),
        ];
    }
}
