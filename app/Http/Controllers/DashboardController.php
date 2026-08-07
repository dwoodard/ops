<?php

namespace App\Http\Controllers;

use App\Models\Objective;
use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $team = Team::where('slug', $request->route('current_team'))->firstOrFail();
        $email = strtolower($request->user()->email);

        $pendingInvitations = TeamInvitation::query()
            ->with(['inviter', 'team'])
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->where(fn ($query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now()))
            ->latest()
            ->get()
            ->map(fn (TeamInvitation $invitation) => [
                'code' => $invitation->code,
                'inviterName' => $invitation->inviter->name,
                'team' => [
                    'name' => $invitation->team->name,
                    'slug' => $invitation->team->slug,
                ],
            ]);

        $objectives = Objective::query()
            ->where('team_id', $team->id)
            ->with(['owner', 'signals', 'opportunities'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Objective $objective) => [
                'id' => $objective->id,
                'name' => $objective->name,
                'status' => $objective->status,
                'goal' => $objective->goal,
                'owner' => [
                    'id' => $objective->owner->id,
                    'name' => $objective->owner->name,
                ],
                'signalCount' => $objective->signals->count(),
                'opportunityCount' => $objective->opportunities->count(),
                'progress' => $objective->progress(),
            ]);

        $stats = [
            'totalObjectives' => Objective::where('team_id', $team->id)->count(),
            'activeObjectives' => Objective::where('team_id', $team->id)->where('status', 'active')->count(),
            'completedObjectives' => Objective::where('team_id', $team->id)->where('status', 'completed')->count(),
        ];

        return Inertia::render('Dashboard', [
            'pendingInvitations' => $pendingInvitations,
            'objectives' => $objectives,
            'stats' => $stats,
        ]);
    }
}
