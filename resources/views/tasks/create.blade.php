@extends('layouts.app')

@section('title', 'New Task')

@section('content')

    <x-back-button :fallback="route('tasks.index')" />

    <div class="mb-4">
        <p class="page-kicker mb-1">Create work</p>
        <h1 class="page-title h3 mb-0">New Task</h1>
    </div>

    @include('tasks.partials.form', [
        'action' => route('tasks.store'),
        'cancelUrl' => route('tasks.index'),
        'submitLabel' => 'Save Task',
    ])
@endsection
