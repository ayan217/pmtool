<div class="d-flex flex-wrap gap-1 task-actions">
    <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-secondary" title="View" aria-label="View">
        <i class="bi bi-eye"></i>
    </a>
    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-secondary" title="Edit" aria-label="Edit">
        <i class="bi bi-pencil"></i>
    </a>
    @unless ($task->isArchived())
        <button class="btn btn-sm btn-outline-dark" type="button" title="Remind" aria-label="Remind" data-bs-toggle="modal" data-bs-target="#remindTask{{ $task->id }}">
            <i class="bi bi-bell"></i>
        </button>
        @push('forms')
            @include('tasks.partials.reminder-modal')
        @endpush
    @endunless

    @if (! $task->isCompleted() && ! $task->isArchived())
        <form method="POST" action="{{ route('tasks.archive', $task) }}">
            @csrf
            <button class="btn btn-sm btn-outline-dark" type="submit" title="Archive" aria-label="Archive">
                <i class="bi bi-archive"></i>
            </button>
        </form>
    @endif

    @if ($task->isArchived())
        <form method="POST" action="{{ route('tasks.restore', $task) }}">
            @csrf
            <button class="btn btn-sm btn-outline-primary" type="submit" title="Restore" aria-label="Restore">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
        </form>
        <button class="btn btn-sm btn-outline-danger" type="button" title="Delete" aria-label="Delete" data-bs-toggle="modal" data-bs-target="#deleteTask{{ $task->id }}">
            <i class="bi bi-trash"></i>
        </button>
        @include('tasks.partials.delete-modal')
    @elseif ($compact ?? false)
        <form method="POST" action="{{ route('tasks.destroy', $task) }}" data-confirm-form="Permanently delete this task?">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete" aria-label="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    @endif
</div>
