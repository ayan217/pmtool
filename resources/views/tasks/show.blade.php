@extends('layouts.app')

@section('title', $task->title)

@section('content')

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="page-kicker mb-1">{{ $task->project?->name ?? 'Standalone task' }}</p>
            <h1 class="page-title h3 mb-0">{{ $task->title }}</h1>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-secondary">Edit</a>
            @unless ($task->isArchived())
                <button class="btn btn-outline-dark" type="button" data-bs-toggle="modal" data-bs-target="#remindTask{{ $task->id }}">Send Reminder</button>
                @push('forms')
                    @include('tasks.partials.reminder-modal')
                @endpush
            @endunless
            @if (! $task->isCompleted() && ! $task->isArchived())
                <form method="POST" action="{{ route('tasks.complete', $task) }}">
                    @csrf
                    <button class="btn btn-success" type="submit">Mark Completed</button>
                </form>
                <form method="POST" action="{{ route('tasks.archive', $task) }}">
                    @csrf
                    <button class="btn btn-outline-dark" type="submit">Archive</button>
                </form>
            @endif
            @if ($task->isArchived())
                <form method="POST" action="{{ route('tasks.restore', $task) }}">
                    @csrf
                    <button class="btn btn-primary" type="submit">Restore</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card pm-card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-secondary small">Project</div>
                    <div>{{ $task->project?->name ?? 'No project' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">Developers</div>
                    @if ($task->developers->isEmpty())
                        <div>Unassigned</div>
                    @else
                        <div class="d-flex flex-column gap-2 mt-1">
                            @foreach ($task->developers as $developer)
                                <div>
                                    <div class="fw-semibold">{{ $developer->name }}</div>
                                    <div class="small text-secondary">
                                        {{ $developer->email ?: 'No email' }}
                                        ·
                                        {{ $developer->phone ?: 'No phone' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="text-secondary small">Status</div>
                    <x-status-badge :status="$task->status" />
                </div>
                <div class="col-md-3">
                    <div class="text-secondary small">Priority</div>
                    <x-priority-badge :priority="$task->priority" />
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">Dev Deadline</div>
                    <x-deadline :date="$task->dev_deadline" :type="\App\Enums\DeadlineType::Dev" :overdue="$task->isDevOverdue()" />
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">Client Deadline</div>
                    <x-deadline :date="$task->client_deadline" :type="\App\Enums\DeadlineType::Client" :overdue="$task->isClientOverdue()" />
                </div>
            </div>
        </div>
    </div>

    <div class="card pm-card mb-4">
        <div class="card-body">
            <h2 class="h6 text-uppercase text-secondary">Description</h2>
            <p class="mb-0" style="white-space: pre-wrap;">{{ $task->description ?: 'No description yet.' }}</p>
        </div>
    </div>

    <div class="card pm-card mb-4">
        <div class="card-body">
            <h2 class="h6 text-uppercase text-secondary">Notes</h2>
            <p class="mb-0" style="white-space: pre-wrap;">{{ $task->notes ?: 'No internal notes yet.' }}</p>
        </div>
    </div>

    <div class="card pm-card mb-4">
        <div class="card-body">
            <h2 class="h6 text-uppercase text-secondary mb-3">Documents</h2>

            @if ($task->attachments->isEmpty())
                <p class="text-secondary">No documents attached yet.</p>
            @else
                @include('tasks.partials.attachment-list', ['task' => $task])
            @endif

            <form method="POST" action="{{ route('tasks.attachments.store', $task) }}" enctype="multipart/form-data" class="mt-3">
                @csrf
                <label class="form-label" for="attachments">Add documents</label>
                <input id="attachments" type="file" name="attachments[]" class="form-control @error('attachments') is-invalid @enderror @error('attachments.0') is-invalid @enderror" multiple>
                @error('attachments') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                @error('attachments.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                <button class="btn btn-dark mt-3" type="submit">Attach</button>
            </form>
        </div>
    </div>

    <div class="card pm-card">
        <div class="card-body">
            <h2 class="h6 text-uppercase text-secondary mb-3">Comments</h2>

            <div id="commentList">
                @forelse ($task->comments as $comment)
                    <article class="comment-item mb-4" data-comment-id="{{ $comment->id }}" data-update-url="{{ route('tasks.comments.update', [$task, $comment]) }}" data-delete-url="{{ route('tasks.comments.destroy', [$task, $comment]) }}">
                        <div class="d-flex justify-content-between gap-3">
                            <div>
                                <div class="fw-semibold">{{ $comment->user?->name }} · {{ $comment->created_at->timezone(config('app.timezone'))->format('M j, Y, g:i A') }}</div>
                                <p class="mb-0 mt-1 comment-text">{{ $comment->comment }}</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-edit-comment>Edit</button>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-delete-comment>Delete</button>
                            </div>
                        </div>
                    </article>
                @empty
                    <p id="commentEmpty" class="text-secondary">No comments yet.</p>
                @endforelse
            </div>

            <form id="commentForm" action="{{ route('tasks.comments.store', $task) }}" data-task-id="{{ $task->id }}" class="mt-3">
                <label class="form-label" for="comment">Add Comment</label>
                <textarea id="comment" name="comment" rows="3" class="form-control" placeholder="Write a comment..." required></textarea>
                <button class="btn btn-dark mt-3" type="submit">Add Comment</button>
            </form>
        </div>
    </div>
@endsection
