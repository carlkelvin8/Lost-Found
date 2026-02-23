<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Replace absolute URLs with relative paths for http://localhost/storage/
    $affected = DB::table('report_photos')
        ->where('photo_url', 'LIKE', 'http://localhost/storage/%')
        ->update([
            'photo_url' => DB::raw("REPLACE(photo_url, 'http://localhost/', '')")
        ]);
    
    echo "Fixed $affected records matching 'http://localhost/storage/'.\n";

    // Also handle 127.0.0.1 just in case
    $affected2 = DB::table('report_photos')
        ->where('photo_url', 'LIKE', 'http://127.0.0.1/storage/%')
        ->update([
            'photo_url' => DB::raw("REPLACE(photo_url, 'http://127.0.0.1/', '')")
        ]);
        
    echo "Fixed $affected2 records matching 'http://127.0.0.1/storage/'.\n";
    
    // Also handle http://localhost:8000 just in case
    $affected3 = DB::table('report_photos')
        ->where('photo_url', 'LIKE', 'http://localhost:8000/storage/%')
        ->update([
            'photo_url' => DB::raw("REPLACE(photo_url, 'http://localhost:8000/', '')")
        ]);
    
    echo "Fixed $affected3 records matching 'http://localhost:8000/storage/'.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
