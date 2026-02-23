<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Check if column exists first to avoid error if run multiple times
    $columns = DB::select("SHOW COLUMNS FROM users LIKE 'remember_token'");
    if (empty($columns)) {
        DB::statement('ALTER TABLE users ADD remember_token VARCHAR(100) NULL AFTER password');
        echo "Column 'remember_token' added successfully.";
    } else {
        echo "Column 'remember_token' already exists.";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
