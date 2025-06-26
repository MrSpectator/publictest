<?php

namespace App\Modules\Logger\Database\Factories;

use App\Modules\Logger\Models\SystemLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class SystemLogFactory extends Factory
{
    protected $model = SystemLog::class;

    public function definition(): array
    {
        return [
            'level' => $this->faker->randomElement(['info', 'warning', 'error', 'debug']),
            'message' => $this->faker->sentence,
            'context' => ['user_id' => $this->faker->randomNumber()],
        ];
    }

    /**
     * Indicate that the log is an emergency.
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => SystemLog::LEVEL_EMERGENCY,
            'category' => SystemLog::CATEGORY_SYSTEM,
            'message' => 'System emergency: ' . $this->faker->sentence(),
            'stack_trace' => $this->faker->paragraphs(3, true)
        ]);
    }

    /**
     * Indicate that the log is an error.
     */
    public function error(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => SystemLog::LEVEL_ERROR,
            'message' => 'Error occurred: ' . $this->faker->sentence(),
            'stack_trace' => $this->faker->paragraphs(2, true)
        ]);
    }

    /**
     * Indicate that the log is a warning.
     */
    public function warning(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => SystemLog::LEVEL_WARNING,
            'message' => 'Warning: ' . $this->faker->sentence()
        ]);
    }

    /**
     * Indicate that the log is info.
     */
    public function info(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => SystemLog::LEVEL_INFO,
            'message' => 'Info: ' . $this->faker->sentence()
        ]);
    }

    /**
     * Indicate that the log is for authentication.
     */
    public function authentication(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => SystemLog::CATEGORY_AUTH,
            'message' => $this->faker->randomElement([
                'User login successful',
                'User logout',
                'Failed login attempt',
                'Password reset requested',
                'Account locked due to failed attempts'
            ])
        ]);
    }

    /**
     * Indicate that the log is for API operations.
     */
    public function api(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => SystemLog::CATEGORY_API,
            'message' => $this->faker->randomElement([
                'API request received',
                'API response sent',
                'API rate limit exceeded',
                'API authentication failed',
                'API endpoint not found'
            ])
        ]);
    }

    /**
     * Indicate that the log is for database operations.
     */
    public function database(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => SystemLog::CATEGORY_DATABASE,
            'message' => $this->faker->randomElement([
                'Database query executed',
                'Database connection established',
                'Database transaction committed',
                'Database backup completed',
                'Database optimization started'
            ])
        ]);
    }

    /**
     * Indicate that the log is for email operations.
     */
    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => SystemLog::CATEGORY_EMAIL,
            'message' => $this->faker->randomElement([
                'Email sent successfully',
                'Email delivery failed',
                'Email template rendered',
                'Email queue processed',
                'Email bounce received'
            ])
        ]);
    }

    /**
     * Indicate that the log is for security events.
     */
    public function security(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => SystemLog::CATEGORY_SECURITY,
            'level' => $this->faker->randomElement([SystemLog::LEVEL_WARNING, SystemLog::LEVEL_ERROR]),
            'message' => $this->faker->randomElement([
                'Suspicious login attempt detected',
                'IP address blocked',
                'CSRF token validation failed',
                'File upload security check failed',
                'SQL injection attempt detected'
            ])
        ]);
    }

    /**
     * Indicate that the log is for performance monitoring.
     */
    public function performance(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => SystemLog::CATEGORY_PERFORMANCE,
            'execution_time' => $this->faker->randomFloat(4, 0.1, 10.0),
            'memory_usage' => $this->faker->numberBetween(1024, 524288),
            'message' => $this->faker->randomElement([
                'Slow query detected',
                'High memory usage alert',
                'Cache hit rate below threshold',
                'Response time exceeded limit',
                'Database connection pool exhausted'
            ])
        ]);
    }
} 