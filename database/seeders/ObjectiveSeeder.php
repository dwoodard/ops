<?php

namespace Database\Seeders;

use App\Models\Objective;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class ObjectiveSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'user@user.com')->first();
        $workspace = Workspace::where('owner_id', $user?->id)->first();

        if ($workspace && $user) {
            Objective::factory()
                ->count(3)
                ->create([
                    'workspace_id' => $workspace->id,
                    'owner_id' => $user->id,
                ]);
        }
    }
}
