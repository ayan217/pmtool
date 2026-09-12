<tr>
    <td>
        <a href="{{ route('tasks.show', $task) }}" class="task-link">{{ $task->title }}</a>
        @if (($task->attachments_count ?? 0) > 0)
            <div class="small text-secondary mt-1"><i class="bi bi-paperclip"></i> {{ $task->attachments_count }} {{ \Illuminate\Support\Str::plural('doc', $task->attachments_count) }}</div>
        @endif
    </td>
    <td>{{ $task->project?->name ?? '—' }}</td>
    <td>{{ $task->developer ?: '—' }}</td>
    <td><x-priority-badge :priority="$task->priority" /></td>
    <td><x-status-badge :status="$task->status" /></td>
    <td><x-deadline :date="$task->dev_deadline" :overdue="$task->isDevOverdue()" /></td>
    <td><x-deadline :date="$task->client_deadline" :overdue="$task->isClientOverdue()" /></td>
    <td class="text-nowrap">
        @include('tasks.partials.actions', ['compact' => true])
    </td>
</tr>
