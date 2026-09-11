@props(['date', 'type' => null, 'overdue' => false])

@if ($date)
    <div {{ $attributes }}>
        @if ($type)
            <span class="badge {{ $type->badgeClass() }} me-1">{{ $type->shortLabel() }}</span>
        @endif
        <span class="{{ $overdue ? 'deadline-overdue' : '' }}">
            {{ $overdue ? 'OVERDUE · ' : '' }}{{ $date->timezone(config('app.timezone'))->format('M j, Y, g:i A') }}
        </span>
    </div>
@else
    <span class="text-secondary">—</span>
@endif
