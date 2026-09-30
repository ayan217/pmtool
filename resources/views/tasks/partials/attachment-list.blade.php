@if ($task->attachments->isNotEmpty())
    <ul class="list-unstyled attachment-list mb-0">
        @foreach ($task->attachments as $attachment)
            <li class="d-flex justify-content-between align-items-center gap-3 py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <i class="bi {{ $attachment->icon() }}"></i>
                    <div class="min-w-0">
                        @if ($attachment->existsOnDisk())
                            <a href="{{ $attachment->isPreviewable() ? route('tasks.attachments.show', [$task, $attachment]) : route('tasks.attachments.download', [$task, $attachment]) }}" class="task-link text-break">{{ $attachment->original_name }}</a>
                            <div class="small text-secondary">{{ $attachment->humanSize() }}</div>
                        @else
                            <div class="task-link text-break">{{ $attachment->original_name }}</div>
                            <div class="small text-danger">File missing after deploy. Re-upload this document.</div>
                        @endif
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-1 attachment-actions">
                    @if ($attachment->existsOnDisk())
                        @if ($attachment->isPreviewable())
                            <a href="{{ route('tasks.attachments.show', [$task, $attachment]) }}" class="btn btn-sm btn-outline-secondary" title="View" aria-label="View">
                                <i class="bi bi-eye"></i>
                            </a>
                        @endif
                        <a href="{{ route('tasks.attachments.download', [$task, $attachment]) }}" class="btn btn-sm btn-outline-dark" title="Download" aria-label="Download">
                            <i class="bi bi-download"></i>
                        </a>
                    @endif
                    @unless ($readOnly ?? false)
                        <button type="submit" class="btn btn-sm btn-outline-danger" form="delete-attachment-{{ $attachment->id }}" title="Remove" aria-label="Remove">
                            <i class="bi bi-trash"></i>
                        </button>
                        @push('forms')
                            <form id="delete-attachment-{{ $attachment->id }}" method="POST" action="{{ route('tasks.attachments.destroy', [$task, $attachment]) }}" data-confirm-form="Remove this document?">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endpush
                    @endunless
                </div>
            </li>
        @endforeach
    </ul>
@endif
