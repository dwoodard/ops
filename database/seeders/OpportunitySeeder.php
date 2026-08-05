<?php

namespace Database\Seeders;

use App\Models\Objective;
use App\Models\Opportunity;
use Illuminate\Database\Seeder;

class OpportunitySeeder extends Seeder
{
    public function run(): void
    {
        $objectives = Objective::all();

        foreach ($objectives as $objective) {
            Opportunity::factory()
                ->count(5)
                ->create(['objective_id' => $objective->id]);
        }
    }
}
