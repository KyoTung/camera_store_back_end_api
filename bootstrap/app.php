<?php
use Illuminate\Filesystem\Filesystem;

$tmpDir = __DIR__.'/../storage/framework/tmp';

if (! is_dir($tmpDir)) {
    mkdir($tmpDir, 0777, true);
}

putenv('TMP='.$tmpDir);
putenv('TEMP='.$tmpDir);
putenv('TMPDIR='.$tmpDir);
// Tiếp tục phần còn lại
$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;



return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'checkRoleAdmin' => \App\Http\Middleware\CheckRoleAdmin::class,
            'checkUser' => \App\Http\Middleware\CheckUser::class
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //

    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
    })
    ->create();
