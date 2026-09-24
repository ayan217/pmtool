<div class="task-status-editor" data-pm-status-editor>
    <button type="button" class="task-status-trigger" data-pm-status-open title="Change status" aria-expanded="false" aria-controls="task-status-form-{{ $task->id }}">
        <x-status-badge :status="$task->status" />
    </button>

    <form id="task-status-form-{{ $task->id }}" method="POST" action="{{ route('tasks.status.update', $task) }}" class="task-status-form" data-pm-status-form hidden>
        @csrf
        @method('PATCH')
        <label class="visually-hidden" for="task-status-{{ $task->id }}">Status for {{ $task->title }}</label>
        <select id="task-status-{{ $task->id }}" name="status" class="form-select form-select-sm" data-original="{{ $task->status->value }}">
            @foreach (\App\Enums\TaskStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected($task->status === $status)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-dark" type="submit">Save</button>
        <button class="btn btn-sm btn-outline-secondary" type="button" data-pm-status-cancel>Cancel</button>
    </form>
</div>
