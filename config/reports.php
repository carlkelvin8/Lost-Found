<?php

return [
    // Never run external AI or notification delivery in the HTTP request.
    'background_connection' => env('REPORT_BACKGROUND_CONNECTION', env('QUEUE_CONNECTION', 'database')) === 'sync'
        ? 'database' : env('REPORT_BACKGROUND_CONNECTION', env('QUEUE_CONNECTION', 'database')),
    'photo_max_kb' => 10240,
    'photo_max_pixels' => 20000000,
];
