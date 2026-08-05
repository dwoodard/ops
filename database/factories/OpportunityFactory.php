<?php

namespace Database\Factories;

use App\Models\Objective;
use App\Models\Opportunity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $entityType = $this->faker->randomElement(['company', 'person', 'partnership', 'event', 'website', 'community', 'other']);

        return [
            'objective_id' => Objective::factory(),
            'name' => match ($entityType) {
                'company' => $this->faker->company(),
                'person' => $this->faker->name(),
                'partnership' => $this->faker->company().' + '.$this->faker->company(),
                'event' => $this->faker->word().' '.ucfirst($this->faker->randomElement(['Conference', 'Summit', 'Expo', 'Workshop'])),
                'website' => $this->faker->domainName(),
                'community' => $this->faker->word().' Community',
                default => $this->faker->word(),
            },
            'entity_type' => $entityType,
            'description' => $this->faker->sentence(10),
            'fit_score' => $this->faker->randomFloat(2, 0.6, 1.0),
            'signal_ids' => [
                $this->faker->randomNumber(4),
                $this->faker->randomNumber(4),
            ],
            'overall_status' => $this->faker->randomElement(['detected', 'researching', 'engaging', 'negotiating', 'closed_won', 'closed_lost']),
            'total_deal_value' => $this->faker->randomFloat(2, 10000, 500000),
            'last_signal_updated_at' => $this->faker->dateTimeThisMonth(),
        ];
    }
}
