<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDeveloper;
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
            'developers' => $this->developerNames(),
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

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task created successfully.');
    }

    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        $task->load(['project', 'comments.user', 'developers']);

        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        $task->load('developers');

        return view('tasks.edit', [
            'task' => $task,
            'projects' => Project::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $data = $request->safe()->only([
            'title',
            'project_id',
            'priority',
            'status',
            'description',
            'notes',
            'dev_deadline',
            'client_deadline',
        ]);

        $status = TaskStatus::from($data['status']);

        if ($status === TaskStatus::Completed && ! $task->isCompleted()) {
            $data['completed_at'] = now();
        }

        if ($status !== TaskStatus::Completed) {
            $data['completed_at'] = $status === TaskStatus::Archived ? $task->completed_at : null;
        }

        if ($status === TaskStatus::Archived && ! $task->isArchived()) {
            $data['previous_status'] = $task->status;
            $data['archived_at'] = now();
        }

        if ($status !== TaskStatus::Archived) {
            $data['archived_at'] = null;
            $data['previous_status'] = null;
        }

        $task->update($data);
        $task->syncDevelopers($request->validated('developers') ?? []);

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task updated successfully.');
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

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function developerNames()
    {
        return TaskDeveloper::query()
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');
    }
}
