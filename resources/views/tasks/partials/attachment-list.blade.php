@if ($task->attachments->isNotEmpty())
    <ul class="list-unstyled attachment-list mb-0">
        @foreach ($task->attachments as $attachment)
            <li class="d-flex justify-content-between align-items-center gap-3 py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <i class="bi {{ $attachment->icon() }}"></i>
                    <div class="min-w-0">
                        @if ($attachment->existsOnDisk())
                            <a href="{{ route('tasks.attachments.download', [$task, $attachment]) }}" class="task-link text-break">{{ $attachment->original_name }}</a>
                            <div class="small text-secondary">{{ $attachment->humanSize() }}</div>
                        @else
                            <div class="task-link text-break">{{ $attachment->original_name }}</div>
                            <div class="small text-danger">File missing after deploy. Re-upload this document.</div>
                        @endif
                    </div>
                </div>
                @unless ($readOnly ?? false)
                    <form method="POST" action="{{ route('tasks.attachments.destroy', [$task, $attachment]) }}" data-confirm-form="Remove this document?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                    </form>
                @endunless
            </li>
        @endforeach
    </ul>
@endif
