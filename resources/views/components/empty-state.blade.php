@props(['title', 'actionUrl' => null, 'actionLabel' => null])

<div class="empty-state">
    <h2 class="h5 mb-2">{{ $title }}</h2>
    <p class="text-secondary mb-3">{{ $slot }}</p>
    @if ($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="btn btn-dark">{{ $actionLabel }}</a>
    @endif
</div>
