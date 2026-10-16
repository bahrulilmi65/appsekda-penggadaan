<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register alias middleware (NOT as global - prevents guard conflicts)
        $middleware->alias([
            'check.role'     => \App\Http\Middleware\CheckRole::class,
            'check.active'   => \App\Http\Middleware\CheckUserActive::class,
            // Spatie - alias tapi JANGAN dipanggil via 'role:' string
            'permission'     => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);

        // Tambahkan CheckUserActive hanya pada web middleware group (bukan global)
        $middleware->web(append: [
            \App\Http\Middleware\CheckUserActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if (!$request->expectsJson()) {
                return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
            }
            return response()->json(['message' => 'Unauthenticated.'], 401);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($e->getStatusCode() === 403 && !$request->expectsJson()) {
                return response()->view('errors.403', ['message' => $e->getMessage()], 403);
            }
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
