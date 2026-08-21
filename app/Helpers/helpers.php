<?php

if (!function_exists('createStatusTimeline')) {
    function createStatusTimeline(string $currentStatus): string
    {
        $statuses = ['pending', 'matched', 'claimed', 'returned'];
        $currentIndex = array_search($currentStatus, $statuses);
        
        $icons = [
            'pending' => 'bi-clock',
            'matched' => 'bi-link',
            'claimed' => 'bi-person-check',
            'returned' => 'bi-check-circle',
        ];
        
        $html = '<div class="status-timeline">';
        
        foreach ($statuses as $i => $status) {
            $className = 'timeline-step';
            if ($i < $currentIndex) {
                $className .= ' completed';
            } elseif ($i === $currentIndex) {
                $className .= ' active';
            }
            
            $icon = $i < $currentIndex ? 'bi-check' : ($icons[$status] ?? 'bi-circle');
            
            $html .= <<<HTML
            <div class="{$className}">
                <div class="timeline-dot">
                    <i class="bi {$icon}"></i>
                </div>
                <span class="timeline-label">{$status}</span>
            </div>
            HTML;
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
