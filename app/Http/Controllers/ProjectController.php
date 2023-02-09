<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('project-index');

        $projects = Project::get();

        return response($projects, 200);
    }

    public function store(Request $request)
    {
        $this->authorize('project-create');

        $request->validate([
            'user_id' => ['nullable'],
            'name' => ['required'],
        ]);

        Project::create($request->all());

        return response(['message' => 'Project created successfully'], 200);
    }

    public function edit(Request $request, Project $project)
    {
        $this->authorize('project-edit');

        if($request->isMethod('POST')){
            $request->validate([
                'user_id' => ['nullable'],
                'name' => ['required'],
            ]);
            $project->update($request->all());

            return response(['message' => 'Project updated successfully'], 200);
        }

        return response($project, 200);
    }

    public function destroy(Project $project)
    {
        $this->authorize('project-delete');

        $project->delete();

        return response(['message' => 'Project deleted successfully'], 200);
    }
}
