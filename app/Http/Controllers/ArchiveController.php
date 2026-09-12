<?php

namespace App\Http\Controllers;

use App\Models\Developer;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArchiveController extends Controller
{
    public function __invoke(Request $request, TaskQuery $taskQuery): View
    {
        $this->authorize('create', Task::class);

        $perPage = (int) $request->input('per_page', config('pm.default_per_page'));

        if (! in_array($perPage, config('pm.per_page_options'), true)) {
            $perPage = (int) config('pm.default_per_page');
        }

        $tasks = $taskQuery
            ->fromRequest($request, 'archived')
            ->orderByDesc('archived_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('archive.index', [
            'tasks' => $tasks,
            'projects' => Project::query()->orderBy('name')->get(),
            'developers' => Developer::suggestionNames(),
            'filters' => $request->query(),
            'perPage' => $perPage,
        ]);
    }
}
