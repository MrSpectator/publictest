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
        $type = fake()->randomElement([1, 2]);
        $base = [
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'company_name' => fake()->company(),
            'company_address' => fake()->address(),
            'company_url' => fake()->url(),
            'country_id' => fake()->numberBetween(1, 200),
            'state_id' => fake()->numberBetween(1, 5000),
            'type' => $type,
        ];
        if ($type === 1) {
            // Individual: fill both personal and company fields
            return array_merge($base, [
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'phone_number' => fake()->phoneNumber(),
            ]);
        } else {
            // Company: only company fields, personal fields null
            return array_merge($base, [
                'first_name' => null,
                'last_name' => null,
                'phone_number' => null,
            ]);
        }
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

    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 1,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone_number' => fake()->phoneNumber(),
            'company_name' => fake()->company(),
            'company_address' => fake()->address(),
            'company_url' => fake()->url(),
            'country_id' => fake()->numberBetween(1, 200),
            'state_id' => fake()->numberBetween(1, 5000),
        ]);
    }

    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 2,
            'first_name' => null,
            'last_name' => null,
            'phone_number' => null,
            'company_name' => fake()->company(),
            'company_address' => fake()->address(),
            'company_url' => fake()->url(),
            'country_id' => fake()->numberBetween(1, 200),
            'state_id' => fake()->numberBetween(1, 5000),
        ]);
    }
}
