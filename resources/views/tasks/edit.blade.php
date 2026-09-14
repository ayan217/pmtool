@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')

    <x-back-button :fallback="route('tasks.show', $task)" />

    <div class="mb-4">
        <p class="page-kicker mb-1">Update work</p>
        <h1 class="page-title h3 mb-0">Edit Task</h1>
    </div>

    @include('tasks.partials.form', [
        'action' => route('tasks.update', $task),
        'method' => 'PUT',
        'cancelUrl' => route('tasks.show', $task),
        'submitLabel' => 'Save Task',
    ])
@endsection
