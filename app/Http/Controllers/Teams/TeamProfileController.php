<?php

namespace App\Http\Controllers\Teams;

use App\Actions\Teams\EnrichTeamProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\OnboardTeamRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamProfileController extends Controller
{
    /**
     * Show the team profile page.
     */
    public function show(Request $request): Response
    {
        $team = $request->user()->currentTeam;

        return Inertia::render('TeamProfile', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'website' => $team->website,
                'description' => $team->description,
                'enriched_data' => $team->enriched_data,
                'pending_enriched_data' => $team->pending_enriched_data,
                'enrichment_status' => $team->enrichment_status,
                'onboarded_at' => $team->onboarded_at,
            ],
        ]);
    }

    /**
     * Enrich the team profile and show review page.
     */
    public function enrich(OnboardTeamRequest $request, EnrichTeamProfile $enrichTeamProfile): RedirectResponse
    {
        $team = $request->user()->currentTeam;
        $website = $request->validated('website');

        // Save user input
        $team->update([
            'website' => $website,
            'description' => $request->validated('description'),
        ]);

        // Fetch AI enrichment (don't save yet - just store as pending)
        try {
            $enrichedData = $enrichTeamProfile->getEnrichment(
                $website,
                $request->validated('description'),
            );

            $team->update([
                'pending_enriched_data' => $enrichedData,
                'enrichment_status' => 'pending_review',
            ]);

            Inertia::flash('toast', ['type' => 'info', 'message' => 'Review AI suggestions below']);
        } catch (\Throwable $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'AI enrichment failed: '.$e->getMessage()]);
        }

        return to_route('team-profile.show', ['current_team' => $team->slug]);
    }

    /**
     * Approve and save the enriched data (with optional edits).
     */
    public function approve(Request $request): RedirectResponse
    {
        $team = $request->user()->currentTeam;

        if ($team->enrichment_status !== 'pending_review') {
            return back()->with('error', 'No pending enrichment to approve');
        }

        // Parse pending enriched data
        $pendingData = is_string($team->pending_enriched_data)
            ? json_decode($team->pending_enriched_data, true)
            : $team->pending_enriched_data;

        // Merge any edited fields from the form
        $finalEnrichedData = array_merge($pendingData, [
            'description' => $request->input('description') ?? $pendingData['description'] ?? null,
            'summary' => $request->input('summary') ?? $pendingData['summary'] ?? null,
            'industry' => $request->input('industry') ?? $pendingData['industry'] ?? null,
            'target_market' => $request->input('target_market') ?? $pendingData['target_market'] ?? null,
        ]);

        $aiDescription = $finalEnrichedData['description'] ?? null;

        // User has reviewed and approved - save the final enriched data
        $team->update([
            'description' => $aiDescription ?: $team->description,
            'enriched_data' => $finalEnrichedData,
            'pending_enriched_data' => null,
            'enrichment_status' => 'approved',
            'onboarded_at' => now(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Team profile saved!']);

        return to_route('team-profile.show', ['current_team' => $team->slug]);
    }

    /**
     * Reject pending enrichment and go back to edit.
     */
    public function reject(Request $request): RedirectResponse
    {
        $team = $request->user()->currentTeam;

        $team->update([
            'pending_enriched_data' => null,
            'enrichment_status' => 'edit',
        ]);

        Inertia::flash('toast', ['type' => 'info', 'message' => 'Edit your input and try again']);

        return to_route('team-profile.show', ['current_team' => $team->slug]);
    }
}
