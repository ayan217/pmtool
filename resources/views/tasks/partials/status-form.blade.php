<form method="POST" action="{{ route('tasks.status.update', $task) }}" class="task-status-form d-flex align-items-center gap-1">
    @csrf
    @method('PATCH')
    <label class="visually-hidden" for="task-status-{{ $task->id }}">Status for {{ $task->title }}</label>
    <select id="task-status-{{ $task->id }}" name="status" class="form-select form-select-sm">
        @foreach (\App\Enums\TaskStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected($task->status === $status)>{{ $status->label() }}</option>
        @endforeach
    </select>
    <button class="btn btn-sm btn-dark" type="submit">Save</button>
</form>
