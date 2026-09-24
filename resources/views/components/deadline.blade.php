@props(['date', 'type' => null, 'overdue' => false])

@php
    $when = $date?->timezone(config('app.timezone'))->format('M j, Y, g:i A');
@endphp

@if ($date)
    <div {{ $attributes->class(['deadline', 'is-overdue' => $overdue]) }}>
        @if ($type)
            <span class="badge {{ $type->badgeClass() }} deadline-type">{{ $type->shortLabel() }}</span>
        @endif
        <span class="deadline-text">
            @if ($overdue)
                <span class="deadline-flag">OVERDUE</span><span class="deadline-dot"> · </span>
            @endif
            <span class="deadline-when">{{ $when }}</span>
        </span>
    </div>
@else
    <span class="text-secondary">—</span>
@endif
