<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LogoutSessionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'database']);
    }

    public function test_post_logout_destroys_the_old_session_and_regenerates_the_csrf_token(): void
    {
        $user = $this->loginAs('Kader Posyandu', 'balita');
        $id = session()->getId();
        $token = session()->token();

        $this->get(route('logout'))->assertMethodNotAllowed();
        $this->forgetSession($id);
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertNotSame($id, session()->getId());
        $this->assertNotSame($token, session()->token());
        $this->assertDatabaseMissing('sessions', ['id' => $id]);

        $this->forgetSession($id);
        $this->get(route('kependudukan.balita.index'))->assertRedirect(route('login'));
        $this->assertModelExists($user);
    }

    #[DataProvider('idleRoles')]
    public function test_activity_renews_the_session_but_it_expires_at_thirty_idle_minutes(string $role, string $module, string $route): void
    {
        $user = $this->loginAs($role, $module);
        $id = session()->getId();
        $this->travel(29)->minutes();
        $this->forgetSession($id);
        $this->get(route($route))->assertOk();
        $this->assertAuthenticatedAs($user);

        $this->travel(30)->minutes();
        $this->forgetSession($id);
        $this->get(route($route))->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get(route('login'))->assertOk()->assertSee('Silakan masuk kembali.');
        $this->assertModelExists($user);
    }

    public static function idleRoles(): array
    {
        return [
            ['Nakes / Bidan', 'kms', 'kms.index'],
            ['Warga / Orang Tua', 'artikel', 'edukasi.artikel.index'],
        ];
    }

    public function test_expired_api_session_returns_json_401_for_reads_and_writes(): void
    {
        $this->loginAs('Kader Posyandu', 'balita');
        $id = session()->getId();
        $this->forgetSession($id);
        $this->get(route('balita.index'))->assertOk();
        $this->travel(31)->minutes();

        $this->forgetSession($id);
        $this->get(route('balita.index'))->assertUnauthorized()->assertJsonStructure(['message']);
        $this->forgetSession($id);
        $this->post(route('balita.store'))->assertUnauthorized()->assertJsonStructure(['message']);
        $this->assertGuest();
    }

    private function loginAs(string $role, string $module): User
    {
        $user = $this->userWithPermissions($role, [$module => ['can_view']]);
        $plain = Str::random(24);
        $user->update(['password' => $plain]);
        $this->post(route('login.store'), ['username' => $user->username, 'password' => $plain])->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(DB::table('sessions')->where('id', session()->getId())->exists());

        return $user;
    }

    private function forgetSession(string $id): void
    {
        auth()->forgetGuards();
        session()->driver()->flush();
        $this->withCookie(config('session.cookie'), $id);
    }
}
