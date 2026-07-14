<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '62'.fake()->unique()->numerify('8##########'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => User::ROLE_MASYARAKAT,
            'provider' => null,
            'provider_id' => null,
            'avatar_url' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (): array => ['role' => User::ROLE_SUPER_ADMIN]);
    }

    public function adminDaerah(): static
    {
        return $this->state(fn (): array => ['role' => User::ROLE_ADMIN_DAERAH]);
    }
}
