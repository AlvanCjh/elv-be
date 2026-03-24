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
            'email' => 'test@example.com',
            'role' => 'supervisor',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'Test Member',
            'email' => 'member@example.com',
            'role' => 'member',
            'password' => bcrypt('password'),
        ]);
    }
}