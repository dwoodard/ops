<?php

namespace App\Ai\Agents\ObjectiveAgent;

use App\Ai\Agents\ObjectiveAgent\Tools\SignalSourceLookupTool;
use App\Models\Objective;
use App\Models\SignalSource;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class ObjectiveAgent implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable, RemembersConversations;

    protected ?Objective $objective = null;

    public function __construct(protected ?array $context = null) {}

    /**
     * Set the objective being enriched.
     */
    public function withObjective(Objective $objective): self
    {
        $this->objective = $objective;

        return $this;
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are an objective enrichment specialist for a prospecting automation system called OPS.

Your job is to take a business objective and create a comprehensive targeting profile and search strategy.

## What You'll Do

1. **Understand the Goal**: Read what the user is trying to accomplish
2. **Build Target Profile**: Define the ideal customer profile
   - Industries
   - Company size (revenue range)
   - Growth stages (Bootstrapped, Funded, Scaling, Launching, etc.)
   - Geographies ({"city, state, country, region"}, or {'utah': ['salt lake city'], 'usa': ['new york', 'san francisco']})
   - Decision maker roles (e.g., VP of Sales, Head of Engineering, CTO, etc.)
3. **Position Services**: Clarify what services/products are being offered
4. **Develop Strategy**: Outline a flexible, high-level approach for finding and prioritizing targets based on the objective
5. **Select Sources**: Pick which signal sources are relevant (from available list in the system) and explain why each is relevant to this objective
6. **Create Search Terms**: For each selected source, define what to search for (e.g., keywords, hashtags, topics, etc.) to find relevant signals
7. **Capture Brand Voice**: How should outreach be communicated? (tone, value propositions, case studies, etc.)

## Available Signal Sources

Use the `lookup_signal_sources` tool to explore available sources:
- `lookup_signal_sources(action: "list_all")` — See all active sources
- `lookup_signal_sources(action: "by_category", category: "hiring")` — Filter by category
- `lookup_signal_sources(action: "details", source_slug: "linkedin")` — Get full details

For EACH source you select:
- Explain why it's relevant to this objective
- Mark priority level: must_have | recommended | optional
- The system will create search terms for it

Do NOT create search terms directly. The system will do that based on your selections.

## Output Structure

You'll provide:
- target_context: industries, company_size, growth_stage, geography, decision_makers
- services_positioned: what's being sold/offered
- strategy: high-level approach (array of bullets)
- sources_selected: which sources + why (NOT search terms yet)
- brand_voice: tone, value props, case studies

Be specific and actionable. Think like someone who knows both business development AND signal detection.
PROMPT;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [
            Message::system($this->instructions()),
            Message::user($this->objective?->description ?? 'No objective description provided.'),
        ];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new SignalSourceLookupTool,
        ];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'target_context' => $schema->object(properties: [
                'industries' => $schema->array(
                    items: $schema->string(),
                    description: 'Target industries (e.g., SaaS, FinTech, Healthcare)'
                )->required(),
                'company_size' => $schema->string(
                    description: 'Target revenue range (e.g., $5M-$250M)'
                )->required(),
                'growth_stage' => $schema->array(
                    items: $schema->string(),
                    description: 'Growth stages (Bootstrapped, Funded, Scaling, Launching, etc.)'
                )->default([]),
                'geography' => $schema->array(
                    items: $schema->string(),
                    description: 'Target geographies'
                )->required(),
                'decision_makers' => $schema->array(
                    items: $schema->string(),
                    description: 'Target job titles/roles'
                )->required(),
            ])->required(),
            'services_positioned' => $schema->array(
                items: $schema->string(),
                description: 'Services or products being positioned'
            )->required(),
            'strategy' => $schema->array(
                items: $schema->string(),
                description: 'High-level strategies for finding targets (bullet points)'
            )->required(),
            'sources_selected' => $schema->array(
                items: $schema->object(properties: [
                    'source_id' => $schema->integer(
                        description: 'ID of the signal source'
                    )->required(),
                    'source_slug' => $schema->string(
                        description: 'Slug of the signal source (linkedin, github-trending, etc.)'
                    )->required(),
                    'reason' => $schema->string(
                        description: 'Why this source is relevant for this objective'
                    )->required(),
                    'priority' => $schema->string(
                        enum: ['must_have', 'recommended', 'optional'],
                        description: 'Priority level for this source'
                    )->default('recommended'),
                ]),
                description: 'Signal sources selected for this objective'
            )->required(),
            'brand_voice' => $schema->object(properties: [
                'tone' => $schema->string(
                    description: 'Brand tone (e.g., professional, consultative, direct, friendly)'
                )->required(),
                'value_props' => $schema->array(
                    items: $schema->string(),
                    description: 'Key value propositions'
                )->required(),
                'case_studies' => $schema->array(
                    items: $schema->string(),
                    description: 'Relevant case studies or examples'
                )->default([]),
            ])->required(),
        ];
    }

    /**
     * Get available signal sources for the agent to consider.
     */
    protected function getAvailableSources(): array
    {
        return SignalSource::where('status', 'active')
            ->orderBy('priority', 'desc')
            ->get()
            ->map(fn (SignalSource $source) => [
                'id' => $source->id,
                'slug' => $source->slug,
                'name' => $source->name,
                'type' => $source->type,
                'category' => $source->category,
                'description' => $source->description,
                'cost_tier' => $source->cost_tier,
                'auth_required' => $source->auth_required,
                'frequency_options' => $source->frequency_options,
            ])
            ->toArray();
    }
}
