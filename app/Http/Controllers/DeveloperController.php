<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeveloperRequest;
use App\Http\Requests\UpdateDeveloperRequest;
use App\Models\Developer;
use App\Models\TaskDeveloper;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeveloperController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Developer::class);

        $developers = Developer::query()->orderBy('name')->get();

        $assignmentCounts = [];

        foreach (TaskDeveloper::query()->get(['name']) as $assignment) {
            foreach (explode(',', (string) $assignment->name) as $name) {
                $key = mb_strtolower(trim($name));

                if ($key === '') {
                    continue;
                }

                $assignmentCounts[$key] = ($assignmentCounts[$key] ?? 0) + 1;
            }
        }

        return view('developers.index', compact('developers', 'assignmentCounts'));
    }

    public function create(): View
    {
        $this->authorize('create', Developer::class);

        return view('developers.create', [
            'developer' => new Developer,
        ]);
    }

    public function store(StoreDeveloperRequest $request): RedirectResponse
    {
        $this->authorize('create', Developer::class);

        Developer::query()->create($request->validated());

        return redirect()
            ->route('developers.index')
            ->with('success', 'Developer saved.');
    }

    public function edit(Developer $developer): View
    {
        $this->authorize('update', $developer);

        return view('developers.edit', compact('developer'));
    }

    public function update(UpdateDeveloperRequest $request, Developer $developer): RedirectResponse
    {
        $this->authorize('update', $developer);

        $developer->update($request->validated());

        return redirect()
            ->route('developers.index')
            ->with('success', 'Developer updated.');
    }

    public function destroy(Developer $developer): RedirectResponse
    {
        $this->authorize('delete', $developer);

        $developer->delete();

        return redirect()
            ->route('developers.index')
            ->with('success', 'Developer removed from the directory.');
    }
}
