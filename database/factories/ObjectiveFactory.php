<?php

namespace Database\Factories;

use App\Models\Objective;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Objective>
 */
class ObjectiveFactory extends Factory
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
            'team_id' => Team::factory(),
            'owner_id' => $user->id,
            'name' => 'Land '.$this->faker->numberBetween(5, 20).' '.$this->faker->word().' Accounts',
            'goal' => $this->faker->sentence(12),
            'enriched_data' => [
                'target_context' => [
                    'industries' => [$this->faker->word(), $this->faker->word()],
                    'company_size' => '$'.$this->faker->numberBetween(1, 500).'M revenue',
                    'growth_stage' => [$this->faker->randomElement(['Scaling', 'Launching', 'Rebranding'])],
                    'geography' => ['United States'],
                    'decision_makers' => ['CEO', 'VP Marketing', 'Founder'],
                ],
                'services_positioned' => $this->faker->randomElements([
                    'Strategy',
                    'Implementation',
                    'Analytics',
                    'Consulting',
                    'Training',
                    'Support',
                ], $this->faker->numberBetween(1, 3)),
                'strategy' => $this->faker->sentences(4),
                'search_terms' => [
                    [
                        'id' => 'search_1',
                        'source' => 'linkedin',
                        'frequency' => 'daily',
                        'executed_by' => 'ai_automation',
                    ],
                ],
                'integrations_used' => ['linkedin', 'news_api', 'web_search'],
            ],
            'end_date' => now()->addWeeks(2),
            'status' => $this->faker->randomElement(['active', 'paused', 'completed']),
        ];
    }
}
