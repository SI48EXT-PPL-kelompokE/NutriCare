<?php

namespace Tests\Browser;

use App\Models\Balita;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Symfony\Component\Process\Process;
use Tests\DuskTestCase;

class PengukuranKmsTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $seed = true;

    /**
     * Test alur lengkap: login, tambah pengukuran, ubah, hapus
     */
    public function test_alur_lengkap_pengukuran_balita()
    {
        // Cek ChromeDriver
        if (! $this->chromeDriverAvailable()) {
            $this->markTestSkipped('ChromeDriver tidak tersedia. Install dengan: php artisan dusk:chrome-driver');
        }

        $roleNakes = Role::where('name_role', 'Nakes / Bidan')->firstOrFail();
        $roleNakes->permissions()->where('module_name', 'kms')->update(['can_delete' => true]);
        $user = User::factory()->create([
            'role_id' => $roleNakes->id,
            'email' => 'nakes@test.com',
            'password' => bcrypt('password'),
        ]);

        $balita = Balita::factory()->create([
            'name' => 'Budi Test',
            'birth_date' => now()->subMonths(12),
        ]);

        $this->browse(function (Browser $browser) use ($user, $balita) {
            // 2. Buka daftar pengukuran
            $browser->loginAs($user)
                ->visit(route('kms.index'))
                ->assertSee('Pengukuran Balita');

            // 3. Klik tambah pengukuran
            $browser->clickLink('Tambah Pengukuran')
                ->assertRouteIs('kms.create')
                ->assertSee('Form Pengukuran');

            // 4. Isi form
            $browser->select('balita_id', $balita->id)
                ->type('date', now()->format('Y-m-d'))
                ->type('berat_badan', '9,5')
                ->type('tinggi_badan', '75,0')
                ->type('lingkar_lengan_atas', '12,5');

            // 5. Cek pratinjau usia muncul
            $browser->waitForText('Usia saat ukur:', 5)
                ->assertSee('12 bulan');

            // 6. Submit
            $browser->press('Simpan Pengukuran')
                ->assertRouteIs('kms.index')
                ->assertSee('Data pengukuran berhasil disimpan')
                ->assertSee('Budi Test')
                ->assertSee('9,5');

            // 7. Lihat detail
            $browser->clickLink('Lihat')
                ->assertSee('Detail Pengukuran')
                ->assertSee('9,5 kg')
                ->assertSee('75,0 cm');

            // 8. Ubah data
            $browser->clickLink('Ubah Data')
                ->assertSee('Ubah Pengukuran')
                ->type('berat_badan', '10,0')
                ->press('Simpan Pengukuran')
                ->assertSee('Data pengukuran berhasil diperbarui')
                ->assertSee('10,0 kg');

            // 9. Hapus dengan konfirmasi
            $browser->press('Hapus')
                ->acceptDialog()
                ->assertRouteIs('kms.index')
                ->assertSee('Data pengukuran berhasil dihapus');
        });
    }

    /**
     * Cek ketersediaan ChromeDriver
     */
    protected function chromeDriverAvailable(): bool
    {
        try {
            $process = new Process(['chromedriver', '--version']);
            $process->run();

            return $process->isSuccessful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
