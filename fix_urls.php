<?php

use App\Models\ClaimDocument;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$docs = ClaimDocument::where('file_url', 'like', 'http://localhost%')->get();

foreach ($docs as $doc) {
    $doc->file_url = str_replace('http://localhost', '', $doc->file_url);
    $doc->save();
    echo "Fixed: " . $doc->file_url . "\n";
}

echo "Done.\n";
