<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSession
{
    public const LAST_ACTIVITY = 'auth.last_activity';

    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('web');
        $user = $guard->user();
        $session = $request->session();
        $now = now()->timestamp;
        $previous = $session->get(self::LAST_ACTIVITY);

        if ($user && (! $user->active_status || ($previous !== null && $now - $previous >= 30 * 60))) {
            $guard->logout();
            $session->invalidate();
            $session->regenerateToken();
            $session->flash('session_status', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
        }

        $response = $next($request);

        if ($guard->check() && ($response->isSuccessful() || $response->isRedirection())) {
            $session->put(self::LAST_ACTIVITY, $now);
        }

        return $response;
    }
}
