<?php

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => fake()->randomElement(UserRole::cases()),
            'remember_token' => Str::random(10),
        ];
    }

    public function root(): static
    {
        return $this->state(fn () => [
            'username' => 'root',
            'password' => '$2y$12$QJNCh9U1HO0mN/O7f53wt.UoX7T7hM8V.rBXFl/g5wIGQ.GnR7BOS',
            'role' => UserRole::ADMIN,
        ]);
    }
}
