<?php

namespace App\Http\Controllers\Teams\TeamProfile;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewEnrichmentController extends Controller
{
    /**
     * Approve and save the enriched data (with optional edits).
     */
    public function store(Request $request): RedirectResponse
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
    public function destroy(Request $request): RedirectResponse
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
