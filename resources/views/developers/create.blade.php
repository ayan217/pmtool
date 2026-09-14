@extends('layouts.app')

@section('title', 'New Developer')

@section('content')

    <x-back-button :fallback="route('developers.index')" />

    <div class="mb-4">
        <p class="page-kicker mb-1">People directory</p>
        <h1 class="page-title h3 mb-0">New Developer</h1>
    </div>

    @include('developers.partials.form', [
        'action' => route('developers.store'),
        'cancelUrl' => route('developers.index'),
        'submitLabel' => 'Save Developer',
    ])
@endsection
