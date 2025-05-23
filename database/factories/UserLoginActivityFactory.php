<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserLoginActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserLoginActivity>
 */
class UserLoginActivityFactory extends Factory {
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserLoginActivity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'user_id' => User::factory(),
            'ip_address' => fake()->ipv4(),
            'location' => fake()->city() . ', ' . fake()->country(),
            'user_agent' => fake()->userAgent(),
            'login_method' => fake()->randomElement(['email', 'github', 'google']),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return $attributes['created_at'];
            },
        ];
    }
}
