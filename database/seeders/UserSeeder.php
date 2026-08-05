<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => bcrypt('asdfasdf'),
        ]);

        // Regular user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@user.com',
            'password' => bcrypt('asdfasdf'),
        ]);

        // Additional test users
        // User::factory(5)->create();
    }
}
