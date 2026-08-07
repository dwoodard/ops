<?php

namespace App\Ai\Agents\ObjectiveAgent\Tools;

use App\Models\SignalSource;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\ToolParameter;

class SignalSourceLookupTool implements Tool
{
    /**
     * Get the name of the tool.
     */
    public function name(): string
    {
        return 'lookup_signal_sources';
    }

    /**
     * Get the description of what this tool does.
     */
    public function description(): string
    {
        return 'Look up available signal sources. Can list all sources, filter by category, or get details about a specific source.';
    }

    /**
     * Get the parameters this tool accepts.
     */
    public function parameters(): array
    {
        return [
            ToolParameter::string('action')
                ->required()
                ->description('Action to perform: "list_all", "by_category", or "details"')
                ->enum(['list_all', 'by_category', 'details']),

            ToolParameter::string('category')
                ->description('Filter by category: hiring, product, code, community, funding, news, content, events, web'),

            ToolParameter::string('source_slug')
                ->description('Source slug to get details (e.g., "linkedin", "github-trending", "product-hunt")'),
        ];
    }

    /**
     * Execute the tool.
     */
    public function execute(array $arguments): string
    {
        $action = $arguments['action'] ?? 'list_all';

        return match ($action) {
            'list_all' => $this->listAll(),
            'by_category' => $this->byCategory($arguments['category'] ?? null),
            'details' => $this->details($arguments['source_slug'] ?? null),
            default => json_encode(['error' => 'Invalid action']),
        };
    }

    /**
     * List all available signal sources.
     */
    private function listAll(): string
    {
        $sources = SignalSource::where('status', 'active')
            ->orderBy('priority', 'desc')
            ->get(['id', 'slug', 'name', 'category', 'cost_tier', 'auth_required', 'description'])
            ->map(fn($s) => [
                'id' => $s->id,
                'slug' => $s->slug,
                'name' => $s->name,
                'category' => $s->category,
                'type' => $s->type,
                'cost_tier' => $s->cost_tier,
                'auth_required' => $s->auth_required,
                'description' => substr($s->description, 0, 100),
            ])
            ->toArray();

        return json_encode([
            'count' => count($sources),
            'sources' => $sources,
        ]);
    }

    /**
     * List sources by category.
     */
    private function byCategory(?string $category): string
    {
        if (!$category) {
            $categories = SignalSource::where('status', 'active')
                ->distinct('category')
                ->pluck('category')
                ->toArray();

            return json_encode([
                'available_categories' => $categories,
                'message' => 'Specify a category to filter sources',
            ]);
        }

        $sources = SignalSource::where('status', 'active')
            ->where('category', $category)
            ->orderBy('priority', 'desc')
            ->get(['id', 'slug', 'name', 'category', 'description', 'cost_tier'])
            ->map(fn($s) => [
                'id' => $s->id,
                'slug' => $s->slug,
                'name' => $s->name,
                'description' => $s->description,
                'cost_tier' => $s->cost_tier,
            ])
            ->toArray();

        return json_encode([
            'category' => $category,
            'count' => count($sources),
            'sources' => $sources,
        ]);
    }

    /**
     * Get details about a specific source.
     */
    private function details(?string $slug): string
    {
        if (!$slug) {
            return json_encode(['error' => 'source_slug is required for details action']);
        }

        $source = SignalSource::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (!$source) {
            return json_encode(['error' => "Source '$slug' not found"]);
        }

        return json_encode([
            'id' => $source->id,
            'slug' => $source->slug,
            'name' => $source->name,
            'type' => $source->type,
            'category' => $source->category,
            'description' => $source->description,
            'url' => $source->url,
            'auth_required' => $source->auth_required,
            'cost_tier' => $source->cost_tier,
            'frequency_options' => $source->frequency_options,
            'setup_instructions' => $source->setup_instructions,
            'priority' => $source->priority,
        ]);
    }
}
