@extends('layouts.app')

@section('title', 'Settings')

@section('content')

    <div class="mb-4">
        <p class="page-kicker mb-1">Account and reminders</p>
        <h1 class="page-title h3 mb-0">Settings</h1>
    </div>

    <form method="POST" action="{{ route('settings.update') }}">
        @csrf
        @method('PUT')

        <div class="card pm-card mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Account</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Name</label>
                        <input id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="current_password">Current Password</label>
                        <input id="current_password" type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror">
                        @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="password">New Password</label>
                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <div class="card pm-card mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Notifications</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="notify_dev_deadlines" value="1" id="notify_dev_deadlines" @checked(old('notify_dev_deadlines', $settings['notify_dev_deadlines']))>
                            <label class="form-check-label" for="notify_dev_deadlines">Dev deadline notifications</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="notify_client_deadlines" value="1" id="notify_client_deadlines" @checked(old('notify_client_deadlines', $settings['notify_client_deadlines']))>
                            <label class="form-check-label" for="notify_client_deadlines">Client deadline notifications</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="reminder_hours_dev">Dev deadline reminder</label>
                        <select id="reminder_hours_dev" name="reminder_hours_dev" class="form-select">
                            @foreach (config('pm.reminder_hours') as $hours)
                                <option value="{{ $hours }}" @selected((int) old('reminder_hours_dev', $settings['reminder_hours_dev']) === $hours)>{{ $hours }} {{ \Illuminate\Support\Str::plural('hour', $hours) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="reminder_hours_client">Client deadline reminder</label>
                        <select id="reminder_hours_client" name="reminder_hours_client" class="form-select">
                            @foreach (config('pm.reminder_hours') as $hours)
                                <option value="{{ $hours }}" @selected((int) old('reminder_hours_client', $settings['reminder_hours_client']) === $hours)>{{ $hours }} {{ \Illuminate\Support\Str::plural('hour', $hours) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button class="btn btn-dark" type="submit">Save Settings</button>
        </div>
    </form>
@endsection
