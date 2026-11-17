<?php

namespace Tests\Browser;

use App\Models\Balita;
use App\Models\Imunisasi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ImunisasiTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test alur lengkap CRUD imunisasi (login, tambah, lihat, ubah, hapus).
     *
     * @return void
     */
    public function test_alur_lengkap_crud_imunisasi()
    {
        // Periksa apakah ChromeDriver tersedia
        if (! file_exists(base_path('vendor/laravel/dusk/bin/chromedriver-win.exe'))) {
            $this->markTestSkipped('ChromeDriver tidak tersedia. Jalankan: php artisan dusk:chrome-driver');
        }

        // Setup data
        $roleNakes = Role::create(['name_role' => 'Nakes / Bidan']);
        $roleNakes->permissions()->create([
            'name' => 'Imunisasi',
            'module_name' => 'imunisasi',
            'can_view' => true,
            'can_create' => true,
            'can_edit' => true,
            'can_delete' => true,
        ]);
        $nakes = User::factory()->create([
            'role_id' => $roleNakes->id,
            'email' => 'nakes@test.com',
            'password' => bcrypt('password'),
        ]);
        $balita = Balita::factory()->create(['name' => 'Test Balita Dusk']);

        $this->browse(function (Browser $browser) use ($nakes, $balita) {
            // Kunjungi daftar imunisasi
            $browser->loginAs($nakes)
                ->visit(route('imunisasi.index'))
                ->assertSee('Riwayat Imunisasi');

            // Tambah imunisasi baru
            $browser->click('@tambah-imunisasi')
                ->whenAvailable('@form-imunisasi', function ($form) use ($balita) {
                    $form->select('balita_id', $balita->id)
                        ->select('jenis_vaksin', 'BCG')
                        ->type('tanggal', now()->format('Y-m-d'))
                        ->type('no_batch', 'DUSK-TEST-001')
                        ->press('Simpan');
                })
                ->assertPathIs(route('imunisasi.index', [], false))
                ->assertSee('berhasil disimpan')
                ->assertSee('DUSK-TEST-001');

            // Lihat detail
            $browser->clickLink('Lihat')
                ->assertSee('Detail Catatan Imunisasi')
                ->assertSee('BCG')
                ->assertSee('DUSK-TEST-001');

            // Ubah data
            $browser->clickLink('Ubah Data')
                ->whenAvailable('@form-imunisasi', function ($form) {
                    $form->type('no_batch', 'DUSK-UPDATED')
                        ->press('Simpan');
                })
                ->assertSee('berhasil diperbarui')
                ->assertSee('DUSK-UPDATED');

            // Hapus dengan konfirmasi
            $browser->press('Hapus')
                ->acceptDialog()
                ->assertSee('berhasil dihapus')
                ->assertDontSee('DUSK-UPDATED');
        });
    }
}
