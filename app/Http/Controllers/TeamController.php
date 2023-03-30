<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('team-index');

        $teams = Team::whereHas('company', function ($q){
                $q->where('id', auth('sanctum')->id());
        })->with('users:id,name,surname,email')->get();

        return response($teams, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('team-create');

        $request->validate([
            'name' => ['required', 'max:255'],
            'users' => ['nullable'],
            'projects' => ['nullable'],
        ]);
        $team = Team::create([
            'name' => $request->name,
            'company_id' => auth('sanctum')->user()->companyID(),
        ]);

        $team->users()->attach($request->users);
        $team->projects()->attach($request->projects);

        return response(['message' => 'Team created successfully'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function show(Team $team)
    {
        $this->authorize('team-edit', $team);

        return response($team->load('users'), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Team $team)
    {
        $this->authorize('team-edit', $team);

        $request->validate([
            'name' => ['required', 'max:255'],
            'users' => ['nullable'],
        ]);
        $team->update([
            'name' => $request->name
        ]);

        $team->users()->sync($request->users);

        return response(['message' => 'Team updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function destroy(Team $team)
    {
        $this->authorize('team-delete');

        $team->delete();

        return response(['message' => 'Team deleted successfully'], 200);
    }
}
