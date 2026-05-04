<?php

namespace Database\Factories;

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
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'karyawan',
            'is_active' => true,
            'locked_until' => null,
            'login_attempts' => 0,
            'last_login' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * State: Admin role.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * State: Pemilik PT role.
     */
    public function pemilikPt(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'pemilik_pt',
        ]);
    }

    /**
     * State: Karyawan role.
     */
    public function karyawan(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'karyawan',
        ]);
    }

    /**
     * State: Inactive account.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * State: Locked account (15 minutes from now).
     */
    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'login_attempts' => 5,
            'locked_until' => now()->addMinutes(15),
        ]);
    }
}
