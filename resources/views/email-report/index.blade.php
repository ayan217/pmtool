@extends('layouts.app')

@section('title', 'Email Report')

@section('content')

    <div class="mb-4">
        <p class="page-kicker mb-1">Sent reminders</p>
        <h1 class="page-title h3 mb-0">Email Report</h1>
    </div>

    <form method="GET" action="{{ route('email-report.index') }}" class="card pm-card mb-4">
        <div class="card-body">
            <div class="quick-filters d-flex flex-wrap gap-2 mb-3">
                <a href="{{ route('email-report.index', request()->except(['type', 'page'])) }}" class="btn btn-sm {{ blank($type) ? 'btn-dark' : 'btn-outline-secondary' }}">All</a>
                @foreach (\App\Enums\EmailLogType::cases() as $option)
                    <a href="{{ request()->fullUrlWithQuery(['type' => $option->value, 'page' => null]) }}" class="btn btn-sm {{ $type === $option->value ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $option->label() }}</a>
                @endforeach
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Search</label>
                    <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Subject, task, recipient...">
                </div>
                @if ($type)
                    <input type="hidden" name="type" value="{{ $type }}">
                @endif
                <div class="col-md-2">
                    <label class="form-label">Per page</label>
                    <select name="per_page" class="form-select">
                        @foreach (config('pm.per_page_options') as $option)
                            <option value="{{ $option }}" @selected((int) $perPage === (int) $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-dark" type="submit">Apply filters</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card pm-card">
        <div class="card-body">
            @if ($logs->isEmpty())
                <x-empty-state title="No emails sent yet.">
                    Automatic deadline reminders and manual status reminders will appear here after they are queued.
                </x-empty-state>
            @else
                <div class="d-none d-lg-block table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Sent</th>
                                <th>Type</th>
                                <th>Task</th>
                                <th>Recipients</th>
                                <th>Subject</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td class="text-nowrap">
                                        {{ $log->sent_at?->timezone(config('app.timezone'))->format('M j, Y, g:i A') }}
                                        <div class="small text-secondary">{{ $log->sent_at?->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $log->type->badgeClass() }}">{{ $log->type->label() }}</span>
                                        @if ($log->deadline_type)
                                            <div class="small text-secondary mt-1">{{ $log->deadline_type->shortLabel() }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($log->task)
                                            <a href="{{ route('tasks.show', $log->task) }}" class="task-link">{{ $log->task->title }}</a>
                                            <div class="small text-secondary">{{ $log->task->project?->name ?? 'No project' }}</div>
                                        @else
                                            <span class="text-secondary">Deleted task</span>
                                        @endif
                                    </td>
                                    <td class="text-break">{{ $log->recipientList() }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($log->subject, 72) }}</td>
                                    <td class="text-nowrap text-end">
                                        <a href="{{ route('email-report.show', $log) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-lg-none">
                    @foreach ($logs as $log)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="d-flex justify-content-between gap-2">
                                <span class="badge {{ $log->type->badgeClass() }}">{{ $log->type->label() }}</span>
                                <span class="small text-secondary">{{ $log->sent_at?->diffForHumans() }}</span>
                            </div>
                            <div class="fw-semibold mt-2">{{ $log->subject }}</div>
                            <div class="small text-secondary mt-1">
                                @if ($log->task)
                                    {{ $log->task->title }}
                                @else
                                    Deleted task
                                @endif
                            </div>
                            <div class="small text-break mt-1">{{ $log->recipientList() }}</div>
                            <a href="{{ route('email-report.show', $log) }}" class="btn btn-sm btn-outline-secondary mt-3">View</a>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
@endsection
