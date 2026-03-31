<?php

use App\Http\Middleware\EnsureAdminRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.role' => EnsureAdminRole::class,
        ]);
        $middleware->preventRequestForgery(except: [
            'store/payment/doku/notify',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (PostTooLargeException $_exception, Request $request) {
            $message = 'Ukuran upload melebihi batas server. Jalankan server dengan: php -d upload_max_filesize=600M -d post_max_size=650M -d memory_limit=1024M artisan serve';
            if ($_exception instanceof PostTooLargeException && $request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return redirect()->back()->withErrors([$message])->withInput();
        });
    })->create();
