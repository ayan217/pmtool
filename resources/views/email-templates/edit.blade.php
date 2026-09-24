@extends('layouts.app')

@section('title', 'Email Template')

@section('content')

    <div class="mb-4">
        <p class="page-kicker mb-1">Manual reminders</p>
        <h1 class="page-title h3 mb-0">Email Template</h1>
    </div>

    <div class="card pm-card">
        <div class="card-body">
            <p class="text-secondary mb-4">This template is used when you tap <strong>Send Reminder</strong> on a task and choose Email. Placeholders are replaced per task.</p>

            <form method="POST" action="{{ route('email-templates.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label" for="subject">Subject</label>
                    <input id="subject" name="subject" value="{{ old('subject', $template['subject']) }}" class="form-control @error('subject') is-invalid @enderror" required maxlength="255">
                    @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Default is the task title via <code>{task.title}</code>.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="body">Body</label>
                    <textarea id="body" name="body" rows="12" class="form-control @error('body') is-invalid @enderror" required maxlength="20000">{{ old('body', $template['body']) }}</textarea>
                    @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="small text-secondary mb-4">
                    Task title and project name are part of the default body. Available placeholders:
                    <code>{task.title}</code>,
                    <code>{project.name}</code>,
                    <code>{task.project}</code>,
                    <code>{task.des}</code>,
                    <code>{remaining.hours}</code>
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-dark" type="submit">Save Template</button>
                </div>
            </form>
        </div>
    </div>
@endsection
