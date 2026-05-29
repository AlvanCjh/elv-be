<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test Supervisor',
            'email' => 'supervisor@example.com',
            'role' => 'supervisor',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'Test ELV',
            'email' => 'elv@example.com',
            'role' => 'elv',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'Test ICT',
            'email' => 'ict@example.com',
            'role' => 'ict',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'Test Business',
            'email' => 'business@example.com',
            'role' => 'business',
            'password' => bcrypt('password'),
        ]);
    }
}