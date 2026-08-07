<?php

namespace App\Http\Controllers\Objectives;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreObjectiveRequest;
use App\Http\Requests\UpdateObjectiveRequest;
use App\Models\Objective;

/** @package App\Http\Controllers\Objectives */
class ObjectiveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $objectives = auth()->user()->currentTeam->objectives()->latest()->paginate(10);

        return inertia('objectives/Index', [
            'objectives' => $objectives,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('objectives/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreObjectiveRequest $request)
    {
        $objective = $request->user()->currentTeam->objectives()->create(
            $request->validated() + ['owner_id' => $request->user()->id]
        );

        return redirect()->route('objectives.show', [$request->user()->currentTeam->slug, $objective->id]);
    }

    /**
     * Display the specified resource.
     */
    public function show($current_team, $objective)
    {
        $objective = Objective::findOrFail($objective);

        return inertia('objectives/Show', [
            'objective' => $objective,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Objective $objective)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateObjectiveRequest $request, Objective $objective)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Objective $objective)
    {
        //
    }
}
