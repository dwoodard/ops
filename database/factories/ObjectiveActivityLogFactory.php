<?php

namespace Database\Factories;

use App\Models\Objective;
use App\Models\ObjectiveActivityLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ObjectiveActivityLog>
 */
class ObjectiveActivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $actionType = $this->faker->randomElement([
            'signal_detected',
            'opportunity_created',
            'integration_queried',
            'learning_updated',
            'proposal_made',
        ]);

        $details = match ($actionType) {
            'signal_detected' => [
                'signal_type' => $this->faker->randomElement(['new_hire', 'product_launch', 'funding', 'expansion']),
                'source' => $this->faker->randomElement(['linkedin', 'news_api', 'crunchbase']),
                'company_name' => $this->faker->company(),
                'relevance_score' => $this->faker->randomFloat(2, 0.5, 1.0),
                'metadata' => ['tags' => [$this->faker->word(), $this->faker->word()]],
            ],
            'opportunity_created' => [
                'company_name' => $this->faker->company(),
                'fit_score' => $this->faker->randomFloat(2, 0.6, 1.0),
                'signal_count' => $this->faker->numberBetween(1, 5),
                'initial_status' => 'detected',
                'metadata' => ['created_from' => 'signal_detection'],
            ],
            'integration_queried' => [
                'integration_type' => $this->faker->randomElement(['linkedin', 'news_api', 'web_search', 'event_tracking']),
                'query_type' => $this->faker->randomElement(['search', 'lookup', 'sync', 'poll']),
                'records_returned' => $this->faker->numberBetween(0, 50),
                'duration_ms' => $this->faker->numberBetween(100, 5000),
                'metadata' => ['endpoint' => $this->faker->word()],
            ],
            'learning_updated' => [
                'learning_type' => $this->faker->randomElement(['success_pattern', 'failure_analysis', 'trend_detection']),
                'confidence' => $this->faker->randomFloat(2, 0.5, 1.0),
                'affected_records' => $this->faker->numberBetween(1, 100),
                'change_log' => $this->faker->sentence(),
                'metadata' => ['model_version' => '1.0'],
            ],
            'proposal_made' => [
                'proposal_type' => $this->faker->randomElement(['outreach', 'strategy', 'content', 'timing']),
                'recipient_count' => $this->faker->numberBetween(1, 10),
                'recipient_emails' => [$this->faker->email(), $this->faker->email()],
                'confidence_score' => $this->faker->randomFloat(2, 0.5, 1.0),
                'metadata' => ['template_used' => $this->faker->word()],
            ],
            default => [
                'change_log' => $this->faker->sentence(),
                'metadata' => $this->faker->word(),
            ],
        };

        return [
            'objective_id' => Objective::factory(),
            'action_type' => $actionType,
            'description' => match ($actionType) {
                'signal_detected' => "Detected {$details['signal_type']} signal from {$details['source']}",
                'opportunity_created' => "Created opportunity for {$details['company_name']}",
                'integration_queried' => "Queried {$details['integration_type']} for {$details['query_type']}",
                'learning_updated' => "Updated learning with {$details['learning_type']}",
                'proposal_made' => "Made {$details['proposal_type']} proposal",
                default => $this->faker->sentence(),
            },
            'details' => $details,
            'status' => $this->faker->randomElement(['success', 'failed', 'pending_review']),
            'timestamp' => $this->faker->dateTimeThisMonth(),
        ];
    }
}
