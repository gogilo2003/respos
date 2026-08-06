<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'role_id' => Role::query()->firstOrCreate(['name' => 'customer'], ['name' => 'customer'])->id,
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => true,
            'last_login_at' => null,
        ];
    }

    public function withRole(string $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::query()->firstOrCreate(['name' => $role], ['name' => $role])->id,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'email_verified_at' => null,
        ]);
    }
}
