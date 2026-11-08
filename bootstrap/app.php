<?php

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }

            return $request->expectsJson();
        });

        $exceptions->render(function (UniqueConstraintViolationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $errorMessage = $e->getMessage();

                if (
                    str_contains($errorMessage, 'orang_tua_nik_unique') ||
                    str_contains($errorMessage, 'orang_tua.nik')
                ) {
                    return response()->json([
                        'message' => 'NIK sudah terdaftar dalam sistem.',
                        'errors' => [
                            'nik' => ['NIK sudah terdaftar dalam sistem.'],
                        ],
                    ], 422);
                }
            }

            return null;
        });
    })->create();
