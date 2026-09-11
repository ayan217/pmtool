@props(['status'])

@php
    $status = $status instanceof \App\Enums\TaskStatus || $status instanceof \App\Enums\ProjectStatus
        ? $status
        : \App\Enums\TaskStatus::tryFrom((string) $status);
@endphp

@if ($status)
    <span {{ $attributes->class(['badge', $status->badgeClass()]) }}>{{ $status->label() }}</span>
@endif
