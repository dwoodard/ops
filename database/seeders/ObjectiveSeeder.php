<?php

namespace Database\Seeders;

use App\Models\Objective;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class ObjectiveSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'user@user.com')->first();

        if ($user) {
            [$blueTeam, $redTeam] = Team::factory()
                ->count(2)
                ->sequence(
                    [
                        'name' => 'Blue company',
                        'slug' => 'blue-company',
                        'is_personal' => false,
                    ],
                    [
                        'name' => 'Red company',
                        'slug' => 'red-company',
                        'is_personal' => false,
                    ],
                )
                ->create()
                ->each(fn (Team $team) => $team->members()->attach($user->id, ['role' => 'owner']))
                ->all();

            $user->update(['current_team_id' => $blueTeam->id]);

            Objective::factory()
                ->count(3)
                ->create([
                    'team_id' => $blueTeam->id,
                    'owner_id' => $user->id,
                ]);
        }
    }
}
