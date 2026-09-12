@extends('layouts.app')

@section('title', 'Tasks')

@section('content')

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="page-kicker mb-1">Work queue</p>
            <h1 class="page-title h3 mb-0">Tasks</h1>
        </div>
        <a href="{{ route('tasks.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> New Task</a>
    </div>

    @include('tasks.partials.filters', ['action' => route('tasks.index')])

    <div class="card pm-card">
        <div class="card-body">
            @if ($tasks->isEmpty())
                <x-empty-state title="No active tasks found." action-url="{{ route('tasks.create') }}" action-label="+ New Task">
                    Create your first task to start tracking your work.
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
                                <th>Status</th>
                                <th>Dev Deadline</th>
                                <th>Client Deadline</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @each('tasks.partials.row', $tasks, 'task')
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
