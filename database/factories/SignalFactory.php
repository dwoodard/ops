<?php

namespace Database\Factories;

use App\Models\Objective;
use App\Models\Signal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Signal>
 */
class SignalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $signalType = $this->faker->randomElement(['new_hire', 'product_launch', 'event', 'funding', 'expansion']);

        return [
            'objective_id' => Objective::factory(),
            'signal_type' => $signalType,
            'source' => $this->faker->randomElement(['linkedin', 'news_api', 'event_tracking', 'crunchbase']),
            'company_name' => $this->faker->company(),
            'description' => $this->faker->sentence(),
            'url' => $this->faker->url(),
            'detected_at' => $this->faker->dateTimeThisMonth(),
            'relevance_score' => $this->faker->randomFloat(2, 0.5, 1.0),
            'metadata' => [
                'source_detail' => $this->faker->word(),
                'tags' => [$this->faker->word(), $this->faker->word()],
            ],
            'actions_and_results' => [
                [
                    'action' => [
                        'action_type' => $this->faker->randomElement(['email', 'call', 'message', 'meeting', 'proposal']),
                        'sent_at' => $this->faker->dateTimeThisMonth()->format('Y-m-d\TH:i:sP'),
                        'recipient' => $this->faker->email(),
                        'details' => [
                            'subject' => $this->faker->sentence(),
                            'body' => $this->faker->paragraph(),
                        ],
                    ],
                    'result' => [
                        'status' => $this->faker->randomElement(['sent', 'opened', 'clicked', 'replied', 'meeting_scheduled', 'meeting_completed', 'won', 'lost', 'no_response', 'in_progress']),
                        'opened_at' => $this->faker->boolean(60) ? $this->faker->dateTimeThisMonth()->format('Y-m-d\TH:i:sP') : null,
                        'replied_at' => $this->faker->boolean(30) ? $this->faker->dateTimeThisMonth()->format('Y-m-d\TH:i:sP') : null,
                        'meeting_date' => $this->faker->boolean(20) ? $this->faker->dateTimeBetween('+1 week', '+2 weeks')->format('Y-m-d\TH:i:sP') : null,
                        'deal_value' => $this->faker->boolean(40) ? $this->faker->randomFloat(2, 10000, 500000) : null,
                        'contract_signed' => $this->faker->boolean(10),
                    ],
                ],
            ],
        ];
    }
}
