@extends('layouts.app')

@section('title', $log->subject)

@section('content')

    <x-back-button :fallback="route('email-report.index')" />

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="page-kicker mb-1">Email report</p>
            <h1 class="page-title h3 mb-2">{{ $log->subject }}</h1>
            <span class="badge {{ $log->type->badgeClass() }}">{{ $log->type->label() }}</span>
        </div>
    </div>

    <div class="card pm-card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="small text-secondary">Sent</div>
                    <div>{{ $log->sent_at?->timezone(config('app.timezone'))->format('M j, Y, g:i A') }}</div>
                    <div class="small text-secondary">{{ $log->sent_at?->diffForHumans() }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-secondary">Task</div>
                    @if ($log->task)
                        <a href="{{ route('tasks.show', $log->task) }}" class="task-link">{{ $log->task->title }}</a>
                        <div class="small text-secondary">{{ $log->task->project?->name ?? 'No project' }}</div>
                    @else
                        <div class="text-secondary">Deleted task</div>
                    @endif
                </div>
                <div class="col-md-4">
                    <div class="small text-secondary">Triggered by</div>
                    <div>{{ $log->user?->name ?? 'Scheduler' }}</div>
                    @if ($log->deadline_type)
                        <div class="small text-secondary">{{ $log->deadline_type->label() }}</div>
                    @endif
                </div>
                <div class="col-12">
                    <div class="small text-secondary">Recipients</div>
                    <div class="text-break">{{ $log->recipientList() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card pm-card">
        <div class="card-body">
            <div class="small text-secondary mb-2">Body</div>
            <div style="white-space: pre-wrap;">{{ $log->displayBody() }}</div>
        </div>
    </div>
@endsection
