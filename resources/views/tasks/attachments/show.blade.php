@extends('layouts.app')

@section('title', $attachment->original_name)

@section('content')

    <div class="attachment-page">
        <x-back-button :fallback="route('tasks.show', $task)" />

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div class="min-w-0">
                <p class="page-kicker mb-1">{{ $task->title }}</p>
                <h1 class="page-title h3 mb-1 text-break">{{ $attachment->original_name }}</h1>
                <div class="small text-secondary">{{ $attachment->humanSize() }}</div>
            </div>
            <a href="{{ route('tasks.attachments.download', [$task, $attachment]) }}" class="btn btn-dark">
                <i class="bi bi-download"></i> Download
            </a>
        </div>

        @if ($attachment->isPdf())
            <div class="attachment-pdf-shell">
                <iframe
                    src="{{ route('tasks.attachments.preview', [$task, $attachment]) }}#view=FitH"
                    class="attachment-preview-frame"
                    title="{{ $attachment->original_name }}"
                    width="100%"
                    height="100%"
                ></iframe>
            </div>
        @else
            <div class="card pm-card">
                <div class="card-body attachment-viewer">
                    @if ($attachment->isImage())
                        <img src="{{ route('tasks.attachments.preview', [$task, $attachment]) }}" alt="{{ $attachment->original_name }}" class="attachment-preview-image">
                    @elseif ($attachment->isVideo())
                        <video src="{{ route('tasks.attachments.preview', [$task, $attachment]) }}" class="attachment-preview-video" controls playsinline></video>
                    @else
                        <p class="text-secondary mb-3">Preview isn’t available for this file type.</p>
                        <a href="{{ route('tasks.attachments.download', [$task, $attachment]) }}" class="btn btn-outline-dark">
                            <i class="bi bi-download"></i> Download file
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection

@if ($attachment->isPdf())
    @push('scripts')
        <script>
            (() => {
                const shell = document.querySelector('.attachment-pdf-shell');
                const frame = shell?.querySelector('.attachment-preview-frame');

                if (!shell || !frame) {
                    return;
                }

                const fit = () => {
                    const rect = shell.getBoundingClientRect();
                    const width = Math.max(320, Math.round(rect.width));
                    const height = Math.max(320, Math.round(rect.height));

                    frame.setAttribute('width', String(width));
                    frame.setAttribute('height', String(height));
                    frame.style.width = `${width}px`;
                    frame.style.height = `${height}px`;
                };

                fit();
                window.addEventListener('resize', fit);
            })();
        </script>
    @endpush
@endif
