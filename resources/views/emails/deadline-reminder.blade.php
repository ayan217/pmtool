<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Deadline approaching</title>
</head>
<body style="font-family: Georgia, serif; color: #1c2430; line-height: 1.5;">
    <p>A task deadline is approaching.</p>

    <p>
        <strong>Task:</strong><br>
        {{ $task->title }}
    </p>

    <p>
        <strong>Project:</strong><br>
        {{ $task->project?->name ?? 'No project' }}
    </p>

    <p>
        <strong>Developers:</strong><br>
        @forelse ($task->developers as $developer)
            {{ $developer->name }}
            @if ($developer->email || $developer->phone)
                ({{ collect([$developer->email, $developer->phone])->filter()->implode(', ') }})
            @endif
            <br>
        @empty
            {{ $task->developer ?: 'Unassigned' }}
        @endforelse
    </p>

    <p>
        <strong>Deadline Type:</strong><br>
        {{ $deadlineType->label() }}
    </p>

    <p>
        <strong>Deadline:</strong><br>
        {{ $deadline->timezone(config('app.timezone'))->format('j M Y, g:i A') }}
    </p>

    <p>
        <strong>Status:</strong><br>
        {{ $task->status->label() }}
    </p>

    <p>
        <strong>Priority:</strong><br>
        {{ $task->priority->label() }}
    </p>

    <p>
        <a href="{{ route('tasks.show', $task) }}">Open this task</a>
    </p>
</body>
</html>
