@extends('layouts.app')

@section('title', 'Email Settings')

@section('content')

    <div class="mb-4">
        <p class="page-kicker mb-1">Outgoing mail</p>
        <h1 class="page-title h3 mb-0">Email Settings</h1>
    </div>

    <div class="card pm-card">
        <div class="card-body">
            <p class="text-secondary mb-4">These values are used for outgoing mail. The admin address can be different from the email you use to log in.</p>

            <form method="POST" action="{{ route('email-settings.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label" for="from_name">From name</label>
                    <input id="from_name" name="from_name" value="{{ old('from_name', $settings['from_name']) }}" class="form-control @error('from_name') is-invalid @enderror" required maxlength="255">
                    @error('from_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Shown as the sender name on emails to developers and on admin reminders.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="admin_email">Admin email address</label>
                    <input id="admin_email" type="email" name="admin_email" value="{{ old('admin_email', $settings['admin_email']) }}" class="form-control @error('admin_email') is-invalid @enderror" required maxlength="255">
                    @error('admin_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Deadline reminders and other admin emails are sent here, not to your login email unless they match.</div>
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-dark" type="submit">Save Email Settings</button>
                </div>
            </form>
        </div>
    </div>
@endsection
