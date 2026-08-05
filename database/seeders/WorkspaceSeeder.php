<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class WorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'user@user.com')->first();

        if ($user) {
            $workspace = Workspace::create([
                'owner_id' => $user->id,
                'name' => fake()->company(),
            ]);

            // Add members
            $workspace->members()->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
