<div class="d-flex flex-wrap gap-1">
    <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-secondary">View</a>
    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-secondary">Edit</a>

    @if (! $task->isCompleted() && ! $task->isArchived())
        <form method="POST" action="{{ route('tasks.complete', $task) }}">
            @csrf
            <button class="btn btn-sm btn-outline-success" type="submit">Complete</button>
        </form>
        <form method="POST" action="{{ route('tasks.archive', $task) }}">
            @csrf
            <button class="btn btn-sm btn-outline-dark" type="submit">Archive</button>
        </form>
    @endif

    @if ($task->isArchived())
        <form method="POST" action="{{ route('tasks.restore', $task) }}">
            @csrf
            <button class="btn btn-sm btn-outline-primary" type="submit">Restore</button>
        </form>
        <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteTask{{ $task->id }}">Delete</button>
        @include('tasks.partials.delete-modal')
    @elseif ($compact ?? false)
        <form method="POST" action="{{ route('tasks.destroy', $task) }}" data-confirm-form="Permanently delete this task?">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
        </form>
    @endif
</div>
