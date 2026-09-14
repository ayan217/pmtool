@extends('layouts.app')

@section('title', 'Edit '.$developer->name)

@section('content')

    <x-back-button :fallback="route('developers.index')" />

    <div class="mb-4">
        <p class="page-kicker mb-1">People directory</p>
        <h1 class="page-title h3 mb-0">Edit {{ $developer->name }}</h1>
    </div>

    @include('developers.partials.form', [
        'action' => route('developers.update', $developer),
        'method' => 'PUT',
        'cancelUrl' => route('developers.index'),
        'submitLabel' => 'Update Developer',
    ])
@endsection
