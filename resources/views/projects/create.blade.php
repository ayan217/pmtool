@extends('layouts.app')

@section('title', 'New Project')

@section('content')

    <x-back-button :fallback="route('projects.index')" />

    <div class="mb-4">
        <p class="page-kicker mb-1">Optional grouping</p>
        <h1 class="page-title h3 mb-0">New Project</h1>
    </div>

    @include('projects.partials.form', [
        'action' => route('projects.store'),
        'cancelUrl' => route('projects.index'),
    ])
@endsection
