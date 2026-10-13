@props(['status'])

@php
    $statuses = ['pending', 'matched', 'claimed', 'returned'];
    $foundIndex = array_search($status, $statuses, true);
    $currentIndex = $foundIndex !== false ? $foundIndex : 0;
    $icons = [
        'pending' => 'bi-clock',
        'matched' => 'bi-link',
        'claimed' => 'bi-person-check',
        'returned' => 'bi-check-circle',
    ];
@endphp

<div class="status-timeline">
    @foreach ($statuses as $index => $step)
        <div @class(['timeline-step', 'completed' => $index < $currentIndex, 'active' => $index === $currentIndex])>
            <div class="timeline-dot">
                <i class="bi {{ $index < $currentIndex ? 'bi-check' : $icons[$step] }}"></i>
            </div>
            <span class="timeline-label">{{ $step }}</span>
        </div>
    @endforeach
</div>
