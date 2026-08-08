<?php

namespace App\Actions\Teams;

use App\Ai\Agents\TeamAgent;
use App\Models\Team;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Enums\Lab;

class EnrichTeamProfile
{
    /**
     * Get enriched data without updating the model (for review workflows).
     */
    public function getEnrichment(string $website, ?string $description = null): array
    {
        $website = $this->normalizeWebsite($website);
        $prompt = $this->buildPrompt($website, $description);

        try {
            $response = (new TeamAgent)->prompt($prompt, provider: Lab::Groq);

            $enrichedData = is_array($response) ? $response : json_decode($response, true) ?? ['summary' => (string) $response];

            Log::info('Team profile enriched successfully', [
                'fields' => count($enrichedData),
                'data' => array_keys($enrichedData),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Team profile enrichment failed, using fallback', [
                'error' => $e->getMessage(),
                'website' => $website,
                'trace' => $e->getTraceAsString(),
            ]);
            $enrichedData = ['summary' => (string) $description ?: 'Company information'];
        }

        return $enrichedData;
    }

    /**
     * Enrich a team profile with AI-generated data (legacy method).
     */
    public function handle(Team $team, string $website, ?string $description = null): Team
    {
        $enrichedData = $this->getEnrichment($website, $description);
        $aiDescription = $enrichedData['summary'] ?? null;

        $team->update([
            'website' => $website,
            'description' => $aiDescription ?? $description ?: null,
            'enriched_data' => $enrichedData,
            'onboarded_at' => now(),
        ]);

        return $team;
    }

    /**
     * Build the research prompt from website and description.
     */
    private function buildPrompt(string $website, ?string $description): string
    {
        $parts = ["Research the company at: {$website}"];

        if ($description) {
            $parts[] = "Additional context: {$description}";
        }

        $parts[] = <<<'Prompt'
Provide a comprehensive structured analysis of this company in valid JSON format with these exact fields:
{
  "industry": "The primary industry or sector",
  "company_size": "Estimated company size (e.g., Startup, Small (1-50), Medium (50-500), Enterprise (500+))",
  "summary": "A brief 1-2 sentence summary of what the company does",
  "description": "A complete 2-3 sentence company description covering what they do, who they serve, and their value proposition",
  "target_market": "The primary target market or customer segment",
  "website_title": "The website's likely title or main heading based on domain and context",
  "website_description": "The website's likely main value proposition or tagline"
}

Ensure ALL fields are populated with substantive content. Return only valid JSON, no additional text.
Prompt;

        return implode("\n\n", $parts);
    }

    /**
     * Normalize website URL by adding https:// if missing.
     */
    private function normalizeWebsite(string $website): string
    {
        if (! preg_match('~^https?://~i', $website)) {
            return 'https://'.$website;
        }

        return $website;
    }
}
