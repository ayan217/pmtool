<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Developer;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request, TaskQuery $taskQuery): View
    {
        $this->authorize('create', Task::class);

        $perPage = (int) $request->input('per_page', config('pm.default_per_page'));

        if (! in_array($perPage, config('pm.per_page_options'), true)) {
            $perPage = (int) config('pm.default_per_page');
        }

        $tasks = $taskQuery
            ->fromRequest($request)
            ->orderBySubmission()
            ->paginate($perPage)
            ->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks,
            'projects' => Project::query()->orderBy('name')->get(),
            'developers' => Developer::suggestionNames(),
            'filters' => $request->query(),
            'perPage' => $perPage,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Task::class);

        return view('tasks.create', [
            'task' => new Task([
                'priority' => TaskPriority::Medium,
                'status' => TaskStatus::Pending,
                'project_id' => request('project_id'),
            ]),
            'projects' => Project::query()->orderBy('name')->get(),
            'developerCatalog' => Developer::catalog(),
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $this->authorize('create', Task::class);

        $task = Task::query()->create($request->safe()->only([
            'title',
            'project_id',
            'priority',
            'status',
            'description',
            'notes',
            'dev_deadline',
            'client_deadline',
        ]));

        $task->syncDevelopers($request->validated('developers') ?? []);
        $task->storeAttachments($request->file('attachments', []));

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task created successfully.')
            ->with('ask_reminder', true);
    }

    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        $task->load(['project', 'comments.user', 'developers', 'attachments']);

        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        $task->load(['developers', 'attachments']);

        return view('tasks.edit', [
            'task' => $task,
            'projects' => Project::query()->orderBy('name')->get(),
            'developerCatalog' => Developer::catalog(),
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $data = $request->safe()->only([
            'title',
            'project_id',
            'priority',
            'description',
            'notes',
            'dev_deadline',
            'client_deadline',
        ]);

        $task->applyStatus(TaskStatus::from($request->validated('status')), $data);
        $task->syncDevelopers($request->validated('developers') ?? []);
        $task->storeAttachments($request->file('attachments', []));

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->applyStatus(TaskStatus::from($request->validated('status')));

        return back()->with('success', 'Task status updated.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task permanently deleted.');
    }

    public function complete(Task $task): RedirectResponse
    {
        $this->authorize('complete', $task);

        $task->complete();

        return back()->with('success', 'Task marked as completed.');
    }

    public function archive(Task $task): RedirectResponse
    {
        $this->authorize('archive', $task);

        $task->archive();

        return back()->with('success', 'Task archived.');
    }

    public function restore(Task $task): RedirectResponse
    {
        $this->authorize('restore', $task);

        $task->restore();

        return back()->with('success', 'Task restored.');
    }
}
