@props([
    'fallback' => null,
])

@php
    $fallbackUrl = $fallback ?? url('/');
@endphp

<button
    type="button"
    class="btn btn-sm btn-outline-secondary mb-3"
    data-pm-back
    data-fallback="{{ $fallbackUrl }}"
    {{ $attributes }}
>
    <i class="bi bi-arrow-left"></i> Back
</button>
