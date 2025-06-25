<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Oluwatoni',
            'email' => 'sobandeoluwatonie@gmail.com',
            'password' => Hash::make('passwordtest'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
    }
}
