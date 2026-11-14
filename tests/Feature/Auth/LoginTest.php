<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_sees_login_form(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk ke Akun')
            ->assertSee('name="username"', false)
            ->assertSee('type="password"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_expired_login_form_redirects_without_replaying_credentials(): void
    {
        $this->app->bind(ValidateCsrfToken::class, function ($app) {
            return new class($app, $app['encrypter']) extends ValidateCsrfToken
            {
                protected function runningUnitTests(): bool
                {
                    return false;
                }
            };
        });
        $password = Str::random(24);

        $this->post(route('login.store'), ['username' => 'uji-sesi', 'password' => $password])
            ->assertStatus(303)
            ->assertRedirect(route('login'))
            ->assertSessionHas('session_status')
            ->assertSessionMissing('_old_input')
            ->assertDontSee($password);
        $this->assertGuest();
    }

    public function test_login_json_csrf_error_does_not_echo_credentials(): void
    {
        $this->mock(ValidateCsrfToken::class, function ($middleware) {
            $middleware->shouldReceive('handle')->once()->andThrow(new TokenMismatchException);
        });
        $password = Str::random(24);

        $this->postJson(route('login.store'), ['username' => 'uji-sesi', 'password' => $password])
            ->assertStatus(419)
            ->assertExactJson(['message' => 'Form masuk sudah kedaluwarsa. Masukkan kembali kredensial Anda.'])
            ->assertDontSee($password);
    }

    #[DataProvider('destinations')]
    public function test_bcrypt_login_starts_a_new_session_and_redirects_to_an_allowed_page(
        string $role,
        string $module,
        string $route,
    ): void {
        $user = $this->userWithPermissions($role, [$module => ['can_view']]);
        $plain = Str::random(24);
        $user->update(['password' => $plain]);
        $hash = $user->fresh()->password;
        $this->assertNotSame($plain, $hash);
        $this->assertSame('bcrypt', Hash::info($hash)['algoName']);
        $this->assertGreaterThanOrEqual(10, Hash::info($hash)['options']['cost']);
        $this->assertTrue(Hash::check($plain, $hash));

        $this->get(route('login'))->assertOk();
        $sessionId = session()->getId();
        $token = session()->token();

        $this->post(route('login.store'), ['username' => $user->username, 'password' => $plain])
            ->assertRedirect(route($route))
            ->assertSessionHas(auth('web')->getName(), $user->id);
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionId, session()->getId());
        $this->assertNotSame($token, session()->token());
        $this->get(route('login'))->assertRedirect(route($route));
        $this->get(route($route))->assertOk();
    }

    public static function destinations(): array
    {
        return [
            ['Admin / Kepala Puskesmas', 'paket_pmt', 'pmt.paket.index'],
            ['Nakes / Bidan', 'kms', 'kms.index'],
            ['Kader Posyandu', 'balita', 'kependudukan.balita.index'],
            ['Warga / Orang Tua', 'artikel', 'edukasi.artikel.index'],
        ];
    }

    public function test_bad_credentials_and_inactive_accounts_return_the_same_error_without_flashing_password(): void
    {
        $user = $this->userWithPermissions('Kader Posyandu', ['balita' => ['can_view']]);
        $plain = Str::random(24);
        $user->update(['password' => $plain]);

        foreach ([[$user->username.'-unknown', $plain], [$user->username, 'invalid']] as [$username, $password]) {
            $this->from(route('login'))->post(route('login.store'), compact('username', 'password'))
                ->assertRedirect(route('login'))
                ->assertSessionHasErrors(['login' => 'Username atau password salah.'])
                ->assertSessionMissing('_old_input.password');
            $this->assertGuest();
        }

        $user->update(['active_status' => false]);
        $this->from(route('login'))->post(route('login.store'), [
            'username' => $user->username,
            'password' => $plain,
        ])->assertSessionHasErrors(['login' => 'Username atau password salah.']);
        $this->assertGuest();
    }

    public function test_required_credentials_have_localized_errors(): void
    {
        $this->post(route('login.store'))->assertSessionHasErrors([
            'username' => 'Username wajib diisi.',
            'password' => 'Password wajib diisi.',
        ]);
        $this->assertGuest();
    }

    public function test_login_with_one_blank_field_is_rejected(): void
    {
        $this->post(route('login.store'), ['username' => 'petugas'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('password');
        $this->post(route('login.store'), ['password' => Str::random(24)])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_unknown_role_or_missing_permission_does_not_get_a_session(): void
    {
        foreach (['Role Pengujian', 'Kader Posyandu'] as $role) {
            $user = $this->userWithPermissions($role, []);
            $user->role->permissions()->delete();
            $plain = Str::random(24);
            $user->update(['password' => $plain]);

            $this->post(route('login.store'), ['username' => $user->username, 'password' => $plain])
                ->assertRedirect(route('login'))
                ->assertSessionHasErrors('login');
            $this->assertGuest();
        }
    }

    public function test_login_is_rate_limited_and_reopens_after_a_minute(): void
    {
        $user = $this->userWithPermissions('Kader Posyandu', ['balita' => ['can_view']]);
        $plain = Str::random(24);
        $user->update(['password' => $plain]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson(route('login.store'), ['username' => $user->username, 'password' => 'invalid'])
                ->assertUnprocessable();
        }

        $payload = ['username' => $user->username, 'password' => $plain];
        $this->postJson(route('login.store'), $payload)->assertTooManyRequests();
        $this->assertGuest();
        $this->travel(61)->seconds();
        $this->post(route('login.store'), $payload)->assertRedirect(route('kependudukan.balita.index'));
        $this->assertAuthenticatedAs($user);
    }
}
