<?php

// Creates disposable fixtures only in the explicitly isolated audit SQLite DB.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$path = dirname(__DIR__, 2) . '/storage/framework/testing/client-audit.sqlite';
if (file_exists($path)) {
    throw new RuntimeException('Audit fixture database already exists; refusing to overwrite it.');
}
if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
touch($path);
foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $path, 'MAIL_MAILER' => 'array', 'CACHE_STORE' => 'array'] as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $_SERVER[$key] = $value;
}
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $path) {
    throw new RuntimeException('Database isolation check failed.');
}
Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
foreach (['audit.admin@gmail.com' => 'admin', 'audit.user@gmail.com' => 'student'] as $email => $role) {
    $user = App\Models\User::factory()->create(['email' => $email, 'password' => Illuminate\Support\Facades\Hash::make('Audit-Test-2026!'), 'is_active' => 1]);
    $user->roles()->firstOrCreate(['name' => $role]);
    App\Models\UserProfile::create(['user_id' => $user->id, 'full_name' => $role === 'admin' ? 'Audit Admin' : 'Audit User', 'user_type' => $role]);
}
echo "Isolated audit fixtures prepared.\n";
