<?php

namespace Database\Seeders;

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
        $this->call(SignalSourceSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(ObjectiveSeeder::class);
        $this->call(SignalSeeder::class);
        $this->call(OpportunitySeeder::class);
        $this->call(ContactSeeder::class);
        $this->call(RecommendationSeeder::class);
        $this->call(ObjectiveActivityLogSeeder::class);
    }
}
