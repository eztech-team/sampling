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

        $projects = Project::whereHas('users', function ($q){
            $q->where('id', auth('sanctum')->id());
        })
            ->orWhereHas('projectPermission', function ($q){
                $q->where('user_id', auth('sanctum')->id());
            })
            ->get();

        return response($projects, 200);
    }

    public function store(Request $request)
    {
        $this->authorize('project-create');

        $data = $request->validate($this->rules());

        $project = Project::create($data);

        $project->users()->attach(auth('sanctum')->id());
        $project->users()->attach($data['users']);

        return response(['message' => 'Project created successfully'], 200);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('project-edit', $project);

            $request->validate($this->rules());

            $project->update($request->all());

            return response(['message' => 'Project updated successfully'], 200);
    }

    public function show(Project $project)
    {
        $this->authorize('project-edit', $project);

        return response($project, 200);
    }

    public function destroy(Project $project)
    {
        $this->authorize('project-delete');

        $project->delete();

        return response(['message' => 'Project deleted successfully'], 200);
    }

    // Общий уровень существенности(general_level) и Рабочий уровень существенности(operating_level)
    public function updateLevel(Request $request, Project $project)
    {
        $this->authorize('project-edit', $project);

        $data = $request->validate([
            'general_level' => ['required', 'integer'],
            'operating_level' => ['required', 'integer'],
        ]);

        if($project->general_level && $project->operating_level){
            return response('Bad Request', 400);
        }

        $project->update($data);

        return response(['message' => 'Project updated successfully'], 200);
    }

    protected function rules(): array
    {
        return [
            'users' => ['nullable'],
            'name' => ['required'],
            'company_id' => ['required', 'exists:companies,id'],
            'start_period' => ['required', 'date'],
            'end_period' => ['required', 'date'],
            'supervisor_conf' => ['boolean'],
            'audit_conf' => ['boolean'],
        ];
    }
}
