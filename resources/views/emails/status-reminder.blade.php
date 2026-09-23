<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subjectLine }}</title>
</head>
<body style="font-family: Georgia, serif; color: #1c2430; line-height: 1.5;">
    <p>
        <strong>Task:</strong><br>
        {{ $task->title }}
    </p>

    @if ($task->project)
        <p>
            <strong>Project:</strong><br>
            {{ $task->project->name }}
        </p>
    @endif

    <div style="white-space: pre-wrap;">{{ $bodyText }}</div>
</body>
</html>
