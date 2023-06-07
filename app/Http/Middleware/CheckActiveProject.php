<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;

class CheckActiveProject
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    //*************************************************\\
    /*
     * Если расчет уже прошло я меняю active на true
     * Что бы мы не могли обнавить данные
     * */

    public function handle(Request $request, Closure $next)
    {
        if ($request->balanceItem) {
            $project = Project::query()->where('id', '=', $request->balanceItem->project_id)->first();
        }  else {
            $project = Project::query()->where('id', '=', $request->project_id)->first();
        }
        if(is_null($project) || $project->active){
           return response(['message' => 'Bad Request'], 400);
        }

        return $next($request);
    }
}
