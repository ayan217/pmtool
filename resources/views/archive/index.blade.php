@extends('layouts.app')

@section('title', 'Archive')

@section('content')

    <div class="mb-4">
        <p class="page-kicker mb-1">Removed from active views</p>
        <h1 class="page-title h3 mb-0">Archive</h1>
    </div>

    @include('tasks.partials.filters', ['action' => route('archive.index')])

    <div class="card pm-card">
        <div class="card-body">
            @if ($tasks->isEmpty())
                <x-empty-state title="Archive is empty.">
                    Archived tasks stay searchable and can be restored at any time.
                </x-empty-state>
            @else
                <div class="d-none d-lg-block table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Developer</th>
                                <th>Priority</th>
                                <th>Archived</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tasks as $task)
                                <tr>
                                    <td><a href="{{ route('tasks.show', $task) }}" class="task-link">{{ $task->title }}</a></td>
                                    <td>{{ $task->project?->name ?? '—' }}</td>
                                    <td>{{ $task->developer ?: '—' }}</td>
                                    <td><x-priority-badge :priority="$task->priority" /></td>
                                    <td>{{ $task->archived_at?->diffForHumans() }}</td>
                                    <td>@include('tasks.partials.actions', ['compact' => true])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-lg-none">
                    @each('tasks.partials.card', $tasks, 'task')
                </div>
                <div class="mt-3">{{ $tasks->links() }}</div>
            @endif
        </div>
    </div>
@endsection
