@props(['priority'])

@php
    $priority = $priority instanceof \App\Enums\TaskPriority
        ? $priority
        : \App\Enums\TaskPriority::tryFrom((string) $priority);
@endphp

@if ($priority)
    <span {{ $attributes->class(['badge', $priority->badgeClass()]) }}>{{ $priority->label() }}</span>
@endif
