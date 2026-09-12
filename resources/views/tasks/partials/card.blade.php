<div class="border rounded-3 p-3 mb-3">
    <a href="{{ route('tasks.show', $task) }}" class="task-link">{{ $task->title }}</a>
    <div class="small text-secondary mt-1">
        {{ $task->project?->name ?? 'No project' }} · {{ $task->developer ?: 'Unassigned' }}
        @if (($task->attachments_count ?? 0) > 0)
            · <i class="bi bi-paperclip"></i> {{ $task->attachments_count }} {{ \Illuminate\Support\Str::plural('doc', $task->attachments_count) }}
        @endif
    </div>
    <div class="d-flex flex-wrap gap-2 my-2">
        <x-priority-badge :priority="$task->priority" />
        <x-status-badge :status="$task->status" />
    </div>
    <div class="small mb-2">
        <x-deadline :date="$task->dev_deadline" :type="\App\Enums\DeadlineType::Dev" :overdue="$task->isDevOverdue()" />
        <x-deadline :date="$task->client_deadline" :type="\App\Enums\DeadlineType::Client" :overdue="$task->isClientOverdue()" />
    </div>
    @include('tasks.partials.actions')
</div>
