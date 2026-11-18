<?php

namespace Tests\Browser;

use App\Models\Balita;
use App\Models\OrangTua;
use App\Models\PengukuranKms;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\StandardLmsSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StatusGiziTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_alur_status_gizi(): void
    {
        try {
            $this->browse(function (Browser $browser) {
                $browser->visit('/');
            });
        } catch (\Throwable $e) {
            $this->markTestSkipped('ChromeDriver/Chrome belum tersedia: ' . $e->getMessage());
        }

        $this->seed(StandardLmsSeeder::class);
        $role = Role::create(['name_role' => 'Nakes / Bidan']);
        // Izin modul kms agar user bisa mengakses route kms
        $role->permissions()->create([
            'module_name' => 'kms',
            'name' => 'kms',
            'can_view' => true,
            'can_create' => true,
            'can_edit' => true,
            'can_delete' => true,
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'email' => 'nakes-status@test.com',
            'password' => bcrypt('password'),
            'active_status' => true,
        ]);
        $orangTua = OrangTua::factory()->create(['name' => 'Ibu Status']);
        $balita = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'name' => 'Balita Status',
            'jenis_kelamin' => 'L',
            'birth_date' => now()->subMonths(24),
        ]);

        $this->browse(function (Browser $browser) use ($user, $balita) {
            $browser->visit(route('login'))
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('Login')
                ->visit(route('kms.create'))
                ->select('balita_id', $balita->id)
                ->type('date', now()->subDay()->format('Y-m-d'))
                ->type('berat_badan', '9,5')
                ->type('tinggi_badan', '82,0')
                ->press('Simpan Pengukuran')
                ->waitForLocation(route('kms.index', [], false))
                ->assertSee('Status Gizi')
                ->visit(route('kms.index', ['status' => 'Normal']))
                ->assertSee('Balita Status');

            $pengukuran = PengukuranKms::latest('id')->first();
            $browser->visit(route('kms.show', $pengukuran))
                ->assertSee('Status Gizi')
                ->assertSee('BB/U')
                ->assertSee('TB/U')
                ->assertSee('BB/TB')
                ->assertSee('alat bantu pemantauan');
        });
    }
}
