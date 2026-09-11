@extends('layouts.app')

@section('title', 'Projects')

@section('content')

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="page-kicker mb-1">Optional containers</p>
            <h1 class="page-title h3 mb-0">Projects</h1>
        </div>
        <a href="{{ route('projects.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Create Project</a>
    </div>

    <div class="card pm-card">
        <div class="card-body">
            @if ($projects->isEmpty())
                <x-empty-state title="No projects yet." action-url="{{ route('projects.create') }}" action-label="+ Create Project">
                    Projects are optional. You can create standalone tasks without a project.
                </x-empty-state>
            @else
                <div class="row g-3">
                    @foreach ($projects as $project)
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('projects.show', $project) }}" class="text-decoration-none text-reset">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="d-flex justify-content-between gap-2">
                                        <h2 class="h5 mb-1">{{ $project->name }}</h2>
                                        <x-status-badge :status="$project->status" />
                                    </div>
                                    <p class="text-secondary small mb-2">{{ $project->tasks_count }} {{ \Illuminate\Support\Str::plural('task', $project->tasks_count) }}</p>
                                    <p class="mb-0 small">{{ \Illuminate\Support\Str::limit($project->description, 110) }}</p>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
