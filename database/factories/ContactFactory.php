<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Opportunity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
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
            'name' => $this->faker->name(),
            'title' => $this->faker->jobTitle(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'source' => $this->faker->randomElement(['linkedin', 'company_website', 'email', 'event', 'referral']),
            'is_direct' => $this->faker->boolean(30),
            'is_decision_maker' => $this->faker->boolean(40),
            'verified' => $this->faker->boolean(60),
        ];
    }
}
