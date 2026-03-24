<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Project;

class ProjectScopeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $projectId = $request->header('X-Project-Id');

        if (!$projectId) {
            return response()->json(['message' => 'Project context is required. Please select a project.'], 400);
        }

        $project = Project::find($projectId);

        if (!$project) {
            return response()->json(['message' => 'The selected project does not exist or you do not have access.'], 403);
        }

        // Share the project ID across the application lifecycle
        app()->instance('active_project_id', $projectId);

        return $next($request);
    }
}