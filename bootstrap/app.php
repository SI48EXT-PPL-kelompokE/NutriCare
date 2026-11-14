<?php

use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToPriorityList(AuthenticatesRequests::class, ValidateCsrfToken::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->routeIs('login.store')) {
                return null;
            }

            if ($e->getPrevious() instanceof TokenMismatchException) {
                $message = 'Form masuk sudah kedaluwarsa. Masukkan kembali kredensial Anda.';

                return $request->expectsJson()
                    ? response()->json(['message' => $message], 419)->header('Cache-Control', 'no-store')
                    : to_route('login', status: 303)->with('session_status', $message)->header('Cache-Control', 'no-store');
            }

            if ($e instanceof ValidationException
                || ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500)) {
                return null;
            }

            $message = 'Terjadi gangguan saat masuk. Silakan coba lagi.';
            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            return $request->expectsJson()
                ? response()->json(['message' => $message], $status)->header('Cache-Control', 'no-store')
                : response($message, $status, ['Cache-Control' => 'no-store']);
        });

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
