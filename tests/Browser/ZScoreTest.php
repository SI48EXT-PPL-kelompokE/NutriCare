<?php

namespace Tests\Browser;

use App\Models\Balita;
use App\Models\OrangTua;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\StandardLmsSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ZScoreTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test: Alur lengkap - login, tambah pengukuran, lihat z-score
     */
    public function test_alur_lengkap_z_score(): void
    {
        // Skip jika ChromeDriver tidak tersedia
        try {
            $this->browse(function (Browser $browser) {
                $browser->visit('/');
            });
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'failed to connect') || 
                str_contains($e->getMessage(), 'ChromeDriver')) {
                $this->markTestSkipped('ChromeDriver tidak tersedia: ' . $e->getMessage());
            }
            throw $e;
        }

        // Seed data
        $this->seed(StandardLmsSeeder::class);
        
        // Setup user dan data
        $role = Role::factory()->create([
            'name_role' => 'Nakes / Bidan',
        ]);
        
        $user = User::factory()->create([
            'role_id' => $role->id,
            'email' => 'nakes@test.com',
            'password' => bcrypt('password'),
            'active_status' => true,
        ]);
        
        $orangTua = OrangTua::factory()->create([
            'name' => 'Ibu Test',
        ]);
        
        $balita = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'name' => 'Balita Test',
            'jenis_kelamin' => 'L',
            'birth_date' => now()->subMonths(12),
        ]);

        $this->browse(function (Browser $browser) use ($user, $balita) {
            // Login (jika autentikasi sudah ada)
            if (route('login', [], false) !== null) {
                $browser->visit(route('login'))
                    ->type('email', 'nakes@test.com')
                    ->type('password', 'password')
                    ->press('Login')
                    ->waitForLocation('/');
            }
            
            // Ke halaman tambah pengukuran
            $browser->visit(route('kms.create'))
                ->assertSee('Tambah Pengukuran')
                
                // Pilih balita
                ->select('balita_id', $balita->id)
                ->pause(500)
                
                // Isi tanggal ukur (hari ini)
                ->type('date', now()->format('Y-m-d'))
                
                // Isi berat badan
                ->type('berat_badan', '9,6')
                
                // Isi tinggi badan
                ->type('tinggi_badan', '75,0')
                
                // Submit
                ->press('Simpan Pengukuran')
                
                // Tunggu redirect ke index
                ->waitForLocation(route('kms.index', [], false))
                ->assertSee('berhasil disimpan');
            
            // Cari pengukuran yang baru dibuat dan buka detail
            $browser->visit(route('kms.index'))
                ->assertSee('Balita Test')
                ->clickLink('Lihat')
                
                // Tunggu halaman detail
                ->pause(500)
                ->assertSee('Detail Pengukuran')
                ->assertSee('Z-Score')
                
                // Cek z-score BB/U tampil
                ->assertSee('Berat Badan menurut Umur (BB/U)')
                ->assertSeeIn('body', 'SD') // Harus ada label "SD"
                
                // Cek z-score TB/U dan BB/TB menampilkan "Belum tersedia"
                ->assertSee('Tinggi/Panjang Badan menurut Umur (TB/U)')
                ->assertSee('Belum tersedia')
                ->assertSee('Tabel acuan WHO belum dimuat');
        });
    }
}
