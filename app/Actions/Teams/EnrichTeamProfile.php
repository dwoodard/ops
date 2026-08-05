<?php

namespace App\Actions\Teams;

use App\Ai\Agents\OnboardTeamAgent;
use App\Models\Team;

class EnrichTeamProfile
{
    /**
     * Enrich a team profile with AI-generated data.
     */
    public function handle(Team $team, string $website, ?string $description = null): Team
    {
        $prompt = $this->buildPrompt($website, $description);

        try {
            $response = (new OnboardTeamAgent)->prompt($prompt);

            $enrichedData = is_array($response) ? $response : json_decode($response, true) ?? ['summary' => (string) $response];
            $aiDescription = $enrichedData['summary'] ?? null;
        } catch (\Throwable $e) {
            $enrichedData = ['summary' => (string) $description ?: 'Company information'];
            $aiDescription = null;
        }

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

        $parts[] = 'Provide structured analysis of this company.';

        return implode("\n\n", $parts);
    }
}
