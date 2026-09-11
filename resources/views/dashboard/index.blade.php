@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="page-kicker mb-1">Today, {{ now()->timezone(config('app.timezone'))->format('l, M j') }}</p>
            <h1 class="page-title h3 mb-0">Command center</h1>
        </div>
        <a href="{{ route('tasks.create') }}" class="btn btn-dark"><i class="bi bi-plus-lg"></i> New Task</a>
    </div>

    <div class="row g-3 mb-4">
        @foreach ([
            ['label' => 'Total Tasks', 'value' => $stats['total'], 'class' => ''],
            ['label' => 'Pending', 'value' => $stats['pending'], 'class' => ''],
            ['label' => 'In Progress', 'value' => $stats['in_progress'], 'class' => ''],
            ['label' => 'Blocked', 'value' => $stats['blocked'], 'class' => ''],
            ['label' => 'Completed', 'value' => $stats['completed'], 'class' => ''],
            ['label' => 'Overdue', 'value' => $stats['overdue'], 'class' => 'overdue'],
        ] as $card)
            <div class="col-6 col-xl-2">
                <div class="card pm-card stat-card {{ $card['class'] }} h-100">
                    <div class="card-body">
                        <div class="text-secondary small mb-2">{{ $card['label'] }}</div>
                        <div class="stat-value">{{ $card['value'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card pm-card h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Overdue Tasks</h2>
                    @forelse ($overdueTasks as $task)
                        <div class="border-bottom py-3">
                            <a href="{{ route('tasks.show', $task) }}" class="task-link">{{ $task->title }}</a>
                            <div class="small mt-1">
                                @if ($task->isDevOverdue())
                                    <x-deadline :date="$task->dev_deadline" :type="\App\Enums\DeadlineType::Dev" :overdue="true" />
                                @endif
                                @if ($task->isClientOverdue())
                                    <x-deadline :date="$task->client_deadline" :type="\App\Enums\DeadlineType::Client" :overdue="true" />
                                @endif
                            </div>
                            <div class="small text-secondary mt-1">
                                Developer: {{ $task->developer ?: 'Unassigned' }}
                                · <x-status-badge :status="$task->status" />
                            </div>
                        </div>
                    @empty
                        <p class="text-secondary mb-0">Nothing is overdue. Keep that streak.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card pm-card h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Due today</h2>
                    @forelse ($todayDeadlines as $row)
                        <div class="d-flex justify-content-between gap-3 border-bottom py-2">
                            <a href="{{ route('tasks.show', $row['task']) }}" class="task-link">{{ $row['task']->title }}</a>
                            <div class="small text-nowrap">
                                <span class="badge {{ $row['type']->badgeClass() }}">{{ $row['type']->shortLabel() }}</span>
                                {{ $row['deadline']->timezone(config('app.timezone'))->format('g:i A') }}
                            </div>
                        </div>
                    @empty
                        <p class="text-secondary mb-0">No deadlines today.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card pm-card">
                <div class="card-body">
                    <h2 class="h5 mb-3">Upcoming Deadlines</h2>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Deadline</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($upcomingDeadlines as $row)
                                    <tr>
                                        <td><a href="{{ route('tasks.show', $row['task']) }}" class="task-link">{{ $row['task']->title }}</a></td>
                                        <td>{{ $row['deadline']->timezone(config('app.timezone'))->calendar() }}</td>
                                        <td><span class="badge {{ $row['type']->badgeClass() }}">{{ $row['type']->shortLabel() }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-secondary">No upcoming deadlines.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card pm-card h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Recently Updated</h2>
                    @forelse ($recentlyUpdated as $task)
                        <div class="d-flex justify-content-between gap-3 border-bottom py-2">
                            <div>
                                <a href="{{ route('tasks.show', $task) }}" class="task-link">{{ $task->title }}</a>
                                <div class="small text-secondary">
                                    {{ $task->project?->name ?? 'No project' }} · {{ $task->developer ?: 'Unassigned' }}
                                </div>
                            </div>
                            <div class="text-end">
                                <x-status-badge :status="$task->status" />
                                <div class="small text-secondary mt-1">{{ $task->updated_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-secondary mb-0">No recent updates.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card pm-card h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Recently Completed</h2>
                    @forelse ($recentlyCompleted as $task)
                        <div class="d-flex justify-content-between gap-3 border-bottom py-2">
                            <div>
                                <a href="{{ route('tasks.show', $task) }}" class="task-link">{{ $task->title }}</a>
                                <div class="small text-secondary">{{ $task->project?->name ?? 'No project' }}</div>
                            </div>
                            <div class="small text-secondary">{{ $task->completed_at?->diffForHumans() }}</div>
                        </div>
                    @empty
                        <p class="text-secondary mb-0">No completed work yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
