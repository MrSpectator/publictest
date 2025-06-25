<?php

namespace App\Modules\Registration\Database\Factories;

use App\Modules\Registration\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $genders = User::getGenders();
        $sources = User::getRegistrationSources();
        
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password123'),
            'username' => $this->faker->unique()->userName(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'date_of_birth' => $this->faker->optional()->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
            'gender' => $this->faker->optional()->randomElement($genders),
            'profile_picture' => $this->faker->optional()->imageUrl(200, 200, 'people'),
            'bio' => $this->faker->optional()->paragraph(),
            'website' => $this->faker->optional()->url(),
            'location' => $this->faker->optional()->city() . ', ' . $this->faker->optional()->country(),
            'timezone' => $this->faker->optional()->timezone(),
            'language' => $this->faker->optional()->randomElement(['en', 'es', 'fr', 'de', 'it']),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'email_verified_at' => $this->faker->optional(80)->dateTimeThisYear(), // 80% chance of being verified
            'phone_verified_at' => $this->faker->optional(60)->dateTimeThisYear(), // 60% chance of being verified
            'last_login_at' => $this->faker->optional(70)->dateTimeThisMonth(), // 70% chance of recent login
            'last_login_ip' => $this->faker->optional()->ipv4(),
            'registration_ip' => $this->faker->ipv4(),
            'registration_source' => $this->faker->randomElement($sources),
            'preferences' => [
                'email_notifications' => $this->faker->boolean(80),
                'sms_notifications' => $this->faker->boolean(30),
                'marketing_emails' => $this->faker->boolean(40),
                'privacy_level' => $this->faker->randomElement(['public', 'friends', 'private']),
                'theme' => $this->faker->randomElement(['light', 'dark', 'auto']),
                'language' => $this->faker->randomElement(['en', 'es', 'fr', 'de', 'it'])
            ],
            'metadata' => [
                'registration_method' => 'email',
                'user_agent' => $this->faker->userAgent(),
                'referrer' => $this->faker->optional()->url(),
                'utm_source' => $this->faker->optional()->randomElement(['google', 'facebook', 'twitter', 'direct']),
                'utm_medium' => $this->faker->optional()->randomElement(['cpc', 'social', 'email', 'organic']),
                'utm_campaign' => $this->faker->optional()->word(),
                'device_type' => $this->faker->randomElement(['desktop', 'mobile', 'tablet']),
                'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
                'os' => $this->faker->randomElement(['Windows', 'macOS', 'Linux', 'iOS', 'Android'])
            ]
        ];
    }

    /**
     * Indicate that the user is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => $this->faker->dateTimeThisYear(),
            'phone_verified_at' => $this->faker->optional(70)->dateTimeThisYear()
        ]);
    }

    /**
     * Indicate that the user is unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'phone_verified_at' => null
        ]);
    }

    /**
     * Indicate that the user is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false
        ]);
    }

    /**
     * Indicate that the user registered recently.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeThisMonth(),
            'last_login_at' => $this->faker->dateTimeThisWeek()
        ]);
    }

    /**
     * Indicate that the user registered via web.
     */
    public function web(): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_source' => User::SOURCE_WEB
        ]);
    }

    /**
     * Indicate that the user registered via mobile.
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_source' => User::SOURCE_MOBILE
        ]);
    }

    /**
     * Indicate that the user registered via API.
     */
    public function api(): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_source' => User::SOURCE_API
        ]);
    }

    /**
     * Indicate that the user registered via social.
     */
    public function social(): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_source' => User::SOURCE_SOCIAL
        ]);
    }

    /**
     * Indicate that the user has a complete profile.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'username' => $this->faker->unique()->userName(),
            'phone' => $this->faker->phoneNumber(),
            'date_of_birth' => $this->faker->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
            'gender' => $this->faker->randomElement(User::getGenders()),
            'profile_picture' => $this->faker->imageUrl(200, 200, 'people'),
            'bio' => $this->faker->paragraph(),
            'website' => $this->faker->url(),
            'location' => $this->faker->city() . ', ' . $this->faker->country(),
            'timezone' => $this->faker->timezone(),
            'language' => $this->faker->randomElement(['en', 'es', 'fr', 'de', 'it']),
            'email_verified_at' => $this->faker->dateTimeThisYear(),
            'phone_verified_at' => $this->faker->dateTimeThisYear()
        ]);
    }

    /**
     * Indicate that the user has a minimal profile.
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'username' => null,
            'phone' => null,
            'date_of_birth' => null,
            'gender' => null,
            'profile_picture' => null,
            'bio' => null,
            'website' => null,
            'location' => null,
            'timezone' => null,
            'language' => null,
            'email_verified_at' => null,
            'phone_verified_at' => null
        ]);
    }

    /**
     * Indicate that the user is male.
     */
    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => User::GENDER_MALE
        ]);
    }

    /**
     * Indicate that the user is female.
     */
    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => User::GENDER_FEMALE
        ]);
    }

    /**
     * Indicate that the user has recently logged in.
     */
    public function recentlyActive(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_login_at' => $this->faker->dateTimeThisWeek(),
            'last_login_ip' => $this->faker->ipv4()
        ]);
    }

    /**
     * Indicate that the user has never logged in.
     */
    public function neverLoggedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_login_at' => null,
            'last_login_ip' => null
        ]);
    }
} 