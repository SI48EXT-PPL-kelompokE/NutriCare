<?php

namespace Tests\Browser;

use App\Models\Balita;
use App\Models\OrangTua;
use App\Models\PengukuranKms;
use App\Models\Role;
use App\Models\User;
use App\Services\StatusGiziService;
use Database\Seeders\StandardLmsSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Dusk test halaman grafik pertumbuhan balita (KF-16).
 *
 * Test ini dilewati (skipped) otomatis bila ChromeDriver/Chrome tidak tersedia,
 * sehingga tidak menggagalkan suite pada lingkungan tanpa peramban.
 */
class GrafikPertumbuhanTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Pastikan Dusk tidak pernah menyentuh database development `nutricare`.
     * Dijalankan sebelum migrasi DatabaseMigrations.
     */
    protected function setUpTraits()
    {
        $this->assertEquals(
            'nutricare_testing',
            config('database.connections.mysql.database'),
            'Dusk hanya boleh berjalan di database nutricare_testing.'
        );

        return parent::setUpTraits();
    }

    public function test_grafik_pertumbuhan_tampil_dan_tab_berganti(): void
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
        $role->permissions()->create([
            'module_name' => 'kms',
            'name' => 'kms',
            'can_view' => true,
            'can_create' => true,
            'can_edit' => true,
            'can_delete' => true,
        ]);
        $role->permissions()->create([
            'module_name' => 'balita',
            'name' => 'balita',
            'can_view' => true,
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id,
            'email' => 'nakes-grafik@test.com',
            'password' => bcrypt('password'),
            'active_status' => true,
        ]);

        $orangTua = OrangTua::factory()->create(['name' => 'Ibu Grafik']);
        $balita = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'name' => 'Balita Grafik',
            'jenis_kelamin' => 'L',
            'birth_date' => '2025-06-01',
        ]);

        // Dua pengukuran agar garis tren muncul.
        foreach ([
            ['2026-01-10', 8.9, 74.0, -0.5, -0.4, -0.3],
            ['2026-03-10', 9.3, 76.0, -0.4, -0.3, -0.2],
        ] as [$tanggal, $berat, $tinggi, $zBbu, $zTbu, $zBbtb]) {
            PengukuranKms::factory()->create([
                'balita_id' => $balita->id,
                'user_id' => $user->id,
                'date' => $tanggal,
                'berat_badan' => $berat,
                'tinggi_badan' => $tinggi,
                'z_score_bbu' => $zBbu,
                'z_score_tbu' => $zTbu,
                'z_score_bbtb' => $zBbtb,
                'status_gizi' => StatusGiziService::tentukan($zBbu, $zTbu, $zBbtb)['status']?->value,
            ]);
        }

        // Anak tanpa pengukuran untuk uji keadaan kosong.
        $balitaKosong = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'name' => 'Balita Kosong',
            'jenis_kelamin' => 'P',
            'birth_date' => '2025-06-01',
        ]);

        $this->browse(function (Browser $browser) use ($user, $balita) {
            $browser->visit(route('login'))
                ->type('username', $user->username)
                ->type('password', 'password')
                ->press('Masuk ke Sistem')
                ->visit(route('balita.pertumbuhan', $balita))
                ->waitFor('#canvasPertumbuhan')
                ->assertSee('Grafik Pertumbuhan')
                ->assertSee('Balita Grafik')
                ->assertSee('Berat / Umur')
                ->assertSee('Tinggi / Umur')
                ->assertSee('BB / TB')
                // Label kartu ringkasan memakai CSS `uppercase`, sehingga teks yang
                // dikembalikan peramban ikut huruf besar -> pencocokan tanpa peduli huruf besar/kecil.
                ->assertSee('BB/U terkini', true)
                ->assertSee('Kenaikan terakhir', true)
                ->assertSee('Riwayat Pengukuran')
                ->assertSee('Cara Ukur', true)
                ->assertSee('alat bantu pemantauan');

            // Instance Chart terbuat: tab BB/U => 4 kurva acuan + 1 hasil ukur.
            // Dibungkus `!!` agar callback mengembalikan nilai boolean yang dapat
            // diserialisasi WebDriver (objek Chart tidak bisa dikembalikan apa adanya).
            $browser->waitUntil('!!(window.growthChart && window.growthChart.instance())');
            $jumlahBbU = (int) $browser->script('return window.growthChart.instance().data.datasets.length;')[0];
            $this->assertSame(5, $jumlahBbU, 'Tab BB/U seharusnya 4 kurva + 1 hasil ukur.');

            // Ganti tab ke Tinggi / Umur: kurva TB/U belum tersedia -> hanya hasil ukur.
            $browser->click('#tab-tb_u')->pause(300);
            $jumlahTbU = (int) $browser->script('return window.growthChart.instance().data.datasets.length;')[0];
            $this->assertSame(1, $jumlahTbU, 'Tab TB/U tanpa kurva acuan seharusnya 1 dataset.');

            // Ganti tab ke BB / TB: kurva juga belum tersedia.
            $browser->click('#tab-bb_tb')->pause(300);
            $browser->assertSee('Kurva acuan WHO untuk indikator ini belum tersedia.');
        });

        // Anak tanpa pengukuran menampilkan pesan kosong.
        $this->browse(function (Browser $browser) use ($user, $balitaKosong) {
            $browser->visit(route('balita.pertumbuhan', $balitaKosong))
                ->assertSee('Belum ada data pengukuran.');
        });
    }
}
