<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workspace>
 */
class WorkspaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::where('email', 'user@user.com')->first()
            ?? User::factory()->create(['email' => 'user@user.com']);

        return [
            'owner_id' => $user->id,
            'name' => $this->faker->company(),
        ];
    }
}
