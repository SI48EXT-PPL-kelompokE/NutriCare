<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureActiveSession;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        return $request->user()
            ? $this->landing($request)
            : response()->view('auth.login')->header('Cache-Control', 'no-store');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->user()) {
            return $this->landing($request);
        }

        $validator = Validator::make(
            $request->only('username', 'password'),
            ['username' => ['required', 'string', 'max:255'], 'password' => ['required', 'string', 'max:255']],
            [
                'username.required' => 'Username wajib diisi.',
                'username.string' => 'Username harus berupa teks.',
                'username.max' => 'Username maksimal 255 karakter.',
                'password.required' => 'Password wajib diisi.',
                'password.string' => 'Password harus berupa teks.',
                'password.max' => 'Password maksimal 255 karakter.',
            ],
        );

        if ($validator->fails()) {
            throw (new ValidationException($validator))->redirectTo(route('login'));
        }

        $credentials = $validator->validated();
        $key = 'login:'.hash('sha256', Str::lower(Str::ascii($credentials['username'])).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'login' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$seconds} detik.",
            ])->status(429)->redirectTo(route('login'));
        }

        if (! Auth::guard('web')->attemptWhen($credentials, fn (User $user) => $user->active_status)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages(['login' => 'Username atau password salah.'])
                ->redirectTo(route('login'));
        }

        RateLimiter::clear($key);
        $request->user()->forceFill(['last_login' => now()])->save();
        $request->session()->regenerate();
        $request->session()->put(EnsureActiveSession::LAST_ACTIVITY, now()->timestamp);

        return $this->landing($request);
    }

    public function landing(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return to_route('login');
        }

        $destination = match ($user->role?->name_role) {
            'Admin / Kepala Puskesmas' => ['pmt.paket.index', 'paket_pmt'],
            'Nakes / Bidan' => ['kms.index', 'kms'],
            'Kader Posyandu' => ['kependudukan.balita.index', 'balita'],
            'Warga / Orang Tua' => ['edukasi.artikel.index', 'artikel'],
            default => null,
        };

        if ($destination && $user->can('access-module', [$destination[1], 'view'])) {
            return to_route($destination[0]);
        }

        if ($user->can('manage-settings')) {
            foreach (['akun' => 'accounts', 'role' => 'roles', 'hak_akses' => 'permissions'] as $module => $route) {
                if ($user->can('access-module', [$module, 'view'])) {
                    return to_route("settings.{$route}.index");
                }
            }
        }

        foreach ([
            ['balita', 'view', 'kependudukan.balita.index'],
            ['orang_tua', 'view', 'kependudukan.orang-tua.index'],
            ['kms', 'view', 'kms.index', 'view-clinical-records'],
            ['imunisasi', 'view', 'imunisasi.index'],
            ['paket_pmt', 'view', 'pmt.paket.index'],
            ['artikel', 'view', 'edukasi.artikel.index'],
            ['konseling', 'view', 'konseling.index'],
            ['konseling', 'create', 'konseling.create'],
        ] as $destination) {
            [$module, $action, $route] = $destination;
            $ability = $destination[3] ?? null;
            if ($user->can('access-module', [$module, $action]) && ($ability === null || $user->can($ability))) {
                return to_route($route);
            }
        }

        return $this->destroy($request)->withErrors([
            'login' => 'Akun Anda belum memiliki halaman yang dapat diakses. Hubungi administrator.',
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }
}
