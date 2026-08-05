<?php

namespace Database\Factories;

use App\Models\Opportunity;
use App\Models\Recommendation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recommendation>
 */
class RecommendationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'opportunity_id' => Opportunity::factory(),
            'recommendation_type' => $this->faker->randomElement(['outreach', 'timing', 'strategy', 'content', 'decision_maker']),
            'content' => [
                'title' => $this->faker->sentence(),
                'body' => $this->faker->paragraph(),
                'action' => $this->faker->word(),
            ],
            'confidence_score' => $this->faker->randomFloat(2, 0.5, 1.0),
            'score_breakdown' => [
                'fit' => $this->faker->randomFloat(2, 0, 1),
                'timing' => $this->faker->randomFloat(2, 0, 1),
                'engagement' => $this->faker->randomFloat(2, 0, 1),
            ],
            'status' => $this->faker->randomElement(['pending', 'accepted', 'rejected', 'completed']),
        ];
    }
}
