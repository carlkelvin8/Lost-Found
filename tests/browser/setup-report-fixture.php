<?php

// Isolated, disposable UI database. Never uses the application's configured database.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$path = storage_path('framework/testing/report-ui.sqlite');
if (! is_dir(dirname($path))) {
    mkdir(dirname($path), 0755, true);
}
if (! is_file($path)) {
    touch($path);
}
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => $path,
    'cache.default' => 'array', 'mail.default' => 'array', 'services.openai.api_key' => null]);
\Illuminate\Support\Facades\DB::purge();
\Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
$user = \App\Models\User::firstOrCreate(['email' => 'report-qa-local-fixture@gmail.com'], [
    'name' => 'Report QA', 'password' => bcrypt('Local-QA-Reports-2026!'), 'is_active' => 1, 'email_verified_at' => now(),
]);
$user->roles()->firstOrCreate(['name' => 'student']);
$user->profile()->firstOrCreate([], ['full_name' => 'Report QA', 'user_type' => 'student']);
foreach (['Bags', 'Electronics', 'Personal items'] as $name) {
    \App\Models\Category::firstOrCreate(['name' => $name]);
}
foreach (['Building A', 'Building B', 'Library'] as $name) {
    \App\Models\Location::firstOrCreate(['name' => $name]);
}
if (in_array('--matches', $argv, true) && ($lost = $user->reports()->where('report_type', 'lost')->first())) {
    foreach (['Blue backpack', 'Blue shoulder bag'] as $name) {
        $found = \App\Models\ItemReport::firstOrCreate(['report_type' => 'found', 'item_name' => $name], [
            'reporter_user_id' => \App\Models\User::where('id', '<>', $user->id)->first()->id,
            'item_description' => 'A blue bag with a silver strap for local QA.',
            'category_id' => \App\Models\Category::where('name', 'Bags')->value('id'),
            'location_detail' => 'Building A Room 301', 'color' => 'Blue', 'status' => 'matched',
        ]);
        \App\Models\ReportMatch::firstOrCreate(['lost_report_id' => $lost->id, 'found_report_id' => $found->id], ['score' => 80, 'method' => 'manual', 'status' => 'suggested']);
    }
}
$photoPath = storage_path('framework/testing/mobile-4032x3024.jpg');
if (! is_file($photoPath)) {
    $image = imagecreatetruecolor(4032, 3024);
    for ($x = 0; $x < 4032; $x += 16) {
        for ($y = 0; $y < 3024; $y += 16) {
            imagefilledrectangle($image, $x, $y, $x + 15, $y + 15, imagecolorallocate($image, ($x / 16) % 256, ($y / 16) % 256, 120));
        }
    }
    imagejpeg($image, $photoPath, 94);
    imagedestroy($image);
    file_put_contents($photoPath, str_repeat("\0", 6 * 1024 * 1024), FILE_APPEND);
}
echo $path.PHP_EOL.$photoPath.PHP_EOL;
