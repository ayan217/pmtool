<tr>
    <td><a href="{{ route('tasks.show', $task) }}" class="task-link">{{ $task->title }}</a></td>
    <td>{{ $task->project?->name ?? '—' }}</td>
    <td>{{ $task->developer ?: '—' }}</td>
    <td><x-priority-badge :priority="$task->priority" /></td>
    <td><x-status-badge :status="$task->status" /></td>
    <td><x-deadline :date="$task->dev_deadline" :overdue="$task->isDevOverdue()" /></td>
    <td><x-deadline :date="$task->client_deadline" :overdue="$task->isClientOverdue()" /></td>
    <td>{{ $task->updated_at->diffForHumans() }}</td>
    <td class="text-nowrap">
        @include('tasks.partials.actions', ['compact' => true])
    </td>
</tr>
