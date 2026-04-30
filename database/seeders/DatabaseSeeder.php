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
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'role' => 'admin',
                'name' => 'Admin User',
                'password' => 'password',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'role' => 'user',
                'name' => 'Regular User',
                'password' => 'password',
            ]
        );
    }
}
