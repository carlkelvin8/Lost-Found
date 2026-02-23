<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    \App\Models\ActivityLog::create([
        'user_id' => 1,
        'action' => 'test_verification',
        'entity_type' => 'test',
        'entity_id' => 1,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'test_agent',
        'meta_json' => '{}'
    ]);
    echo "VERIFICATION_SUCCESS";
} catch (\Exception $e) {
    echo "VERIFICATION_FAILED: " . $e->getMessage();
}
