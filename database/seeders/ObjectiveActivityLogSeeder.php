<?php

namespace Database\Seeders;

use App\Models\Objective;
use App\Models\ObjectiveActivityLog;
use Illuminate\Database\Seeder;

class ObjectiveActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $objectives = Objective::all();

        foreach ($objectives as $objective) {
            ObjectiveActivityLog::factory()
                ->count(5)
                ->create(['objective_id' => $objective->id]);
        }
    }
}
