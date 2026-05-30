<?php
$token = $_GET['token'] ?? '';
if ($token !== getenv('DEPLOY_SECRET_TOKEN')) {
    die('Unauthorized');
}
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)
    ->bootstrap();
Artisan::call('view:clear');
Artisan::call('config:clear');
Artisan::call('route:clear');
Artisan::call('view:cache');
Artisan::call('config:cache');
Artisan::call('route:cache');
echo json_encode([
    'status' => 'ok',
    'time' => now()->toDateTimeString(),
    'message' => 'Cache temizlendi ve yeniden oluşturuldu'
]);
