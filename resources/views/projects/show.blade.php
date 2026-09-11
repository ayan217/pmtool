@extends('layouts.app')

@section('title', $project->name)

@section('content')

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="page-kicker mb-1">Project</p>
            <h1 class="page-title h3 mb-2">{{ $project->name }}</h1>
            <x-status-badge :status="$project->status" />
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="btn btn-dark">Add Task</a>
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-secondary">Edit</a>
            @if ($project->status !== \App\Enums\ProjectStatus::Completed)
                <form method="POST" action="{{ route('projects.complete', $project) }}">
                    @csrf
                    <button class="btn btn-outline-success" type="submit">Complete</button>
                </form>
            @endif
            @if ($project->status !== \App\Enums\ProjectStatus::Archived)
                <form method="POST" action="{{ route('projects.archive', $project) }}">
                    @csrf
                    <button class="btn btn-outline-dark" type="submit">Archive</button>
                </form>
            @endif
            <form method="POST" action="{{ route('projects.destroy', $project) }}" data-confirm-form="Delete this project? Tasks will become standalone.">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger" type="submit">Delete</button>
            </form>
        </div>
    </div>

    @if ($project->description)
        <div class="card pm-card mb-4">
            <div class="card-body" style="white-space: pre-wrap;">{{ $project->description }}</div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        @foreach ([
            ['label' => 'Tasks', 'value' => $counts['total']],
            ['label' => 'Pending', 'value' => $counts['pending']],
            ['label' => 'In Progress', 'value' => $counts['in_progress']],
            ['label' => 'Completed', 'value' => $counts['completed']],
        ] as $card)
            <div class="col-6 col-md-3">
                <div class="card pm-card stat-card">
                    <div class="card-body">
                        <div class="text-secondary small mb-2">{{ $card['label'] }}</div>
                        <div class="stat-value">{{ $card['value'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card pm-card">
        <div class="card-body">
            <h2 class="h5 mb-3">Tasks</h2>
            @forelse ($tasks as $task)
                <div class="d-flex justify-content-between gap-3 border-bottom py-2">
                    <div>
                        <a href="{{ route('tasks.show', $task) }}" class="task-link">{{ $task->title }}</a>
                        <div class="small text-secondary">{{ $task->developer ?: 'Unassigned' }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <x-priority-badge :priority="$task->priority" />
                        <x-status-badge :status="$task->status" />
                    </div>
                </div>
            @empty
                <p class="text-secondary mb-0">No tasks in this project yet.</p>
            @endforelse
        </div>
    </div>
@endsection
