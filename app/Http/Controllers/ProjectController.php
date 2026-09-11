<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Services\TaskQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $this->authorize('create', Project::class);

        $projects = Project::query()
            ->withCount('tasks')
            ->orderByRaw("CASE status WHEN 'active' THEN 0 WHEN 'completed' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->get();

        return view('projects.index', compact('projects'));
    }

    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('projects.create', [
            'project' => new Project(['status' => ProjectStatus::Active]),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $project = Project::query()->create($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created.');
    }

    public function show(Request $request, Project $project, TaskQuery $taskQuery): View
    {
        $this->authorize('view', $project);

        $filters = array_merge($request->all(), ['project' => $project->id]);

        $tasks = $taskQuery
            ->apply($project->tasks()->getQuery()->with('project'), $filters, 'all')
            ->orderByDesc('updated_at')
            ->get();

        return view('projects.show', [
            'project' => $project,
            'counts' => $project->taskCounts(),
            'tasks' => $tasks,
        ]);
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $project->update($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted.');
    }

    public function complete(Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $project->markCompleted();

        return back()->with('success', 'Project marked as completed.');
    }

    public function archive(Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $project->archive();

        return back()->with('success', 'Project archived.');
    }
}
