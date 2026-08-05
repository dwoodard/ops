<?php

namespace Database\Seeders;

use App\Models\Opportunity;
use App\Models\Recommendation;
use Illuminate\Database\Seeder;

class RecommendationSeeder extends Seeder
{
    public function run(): void
    {
        $opportunities = Opportunity::all();

        foreach ($opportunities as $opportunity) {
            Recommendation::factory()
                ->count(random_int(2, 5))
                ->create(['opportunity_id' => $opportunity->id]);
        }
    }
}
