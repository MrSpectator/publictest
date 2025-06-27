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

        \App\Models\User::factory()->individual()->create([
            'first_name' => 'Oluwatoni',
            'last_name' => 'Sobande',
            'email' => 'sobandeoluwatonie@gmail.com',
            'phone_number' => '08164175444495',
            'company_address' => 'lagos',
            'company_url' => 'https://lagos-company.com',
            'country_id' => 159,
            'state_id' => 2855,
            'company_name' => 'efelicod3@gmail.com',
            'password' => Hash::make('passwordtest'),
            'type' => 1,
            'is_active' => true,
        ]);

        \App\Models\User::factory()->company()->create([
            'first_name' => null,
            'last_name' => null,
            'email' => 'contact@acme.example.com',
            'phone_number' => null,
            'company_name' => 'Acme Corp',
            'company_address' => '123 Acme St, Lagos',
            'company_url' => 'https://acme.example.com',
            'country_id' => 159,
            'state_id' => 2855,
            'password' => Hash::make('companypass'),
            'type' => 2,
            'is_active' => true,
        ]);
    }
}
