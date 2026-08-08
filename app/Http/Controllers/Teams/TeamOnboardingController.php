<?php

namespace App\Http\Controllers\Teams;

use App\Actions\Teams\EnrichTeamProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\OnboardTeamRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamOnboardingController extends Controller
{
    /**
     * Show the team onboarding page.
     */
    public function edit(Request $request): Response
    {
        $team = $request->user()->currentTeam;

        return Inertia::render('onboarding/Team', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'website' => $team->website,
                'description' => $team->description,
            ],
        ]);
    }

    /**
     * Store the onboarding data and enrich the team profile.
     */
    public function update(OnboardTeamRequest $request, EnrichTeamProfile $enrichTeamProfile): RedirectResponse
    {
        $team = $request->user()->currentTeam;
        $website = $request->validated('website');

        $enrichTeamProfile->handle(
            $team,
            $website,
            $request->validated('description'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Team setup complete!']);

        return to_route('dashboard.index', ['current_team' => $team->slug]);
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
