@extends('layouts.app')

@section('title', 'Edit Project')

@section('content')

    <div class="mb-4">
        <p class="page-kicker mb-1">Update project</p>
        <h1 class="page-title h3 mb-0">Edit Project</h1>
    </div>

    @include('projects.partials.form', [
        'action' => route('projects.update', $project),
        'method' => 'PUT',
        'cancelUrl' => route('projects.show', $project),
    ])
@endsection
