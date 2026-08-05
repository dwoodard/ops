<?php

namespace Database\Seeders;

use App\Models\Objective;
use App\Models\Signal;
use Illuminate\Database\Seeder;

class SignalSeeder extends Seeder
{
    public function run(): void
    {
        $objectives = Objective::all();

        foreach ($objectives as $objective) {
            Signal::factory()
                ->count(random_int(2, 10))
                ->create(['objective_id' => $objective->id]);
        }
    }
}
