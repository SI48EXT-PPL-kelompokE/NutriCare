<?php

namespace Tests\Feature;

use App\Models\Balita;
use App\Models\OrangTua;
use App\Models\PengukuranKms;
use App\Models\Role;
use App\Models\StandardLms;
use App\Models\User;
use App\Services\ZScoreService;
use Database\Seeders\StandardLmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Feature test untuk integrasi z-score dengan database
 * 
 * Test ini memverifikasi:
 * - Lookup data LMS dari database
 * - Perhitungan z-score untuk pengukuran real
 * - Integrasi dengan controller HTTP
 * - Command hitung ulang
 * - Kinerja
 */
class ZScoreIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Pastikan menggunakan database testing
        $this->assertEquals('nutricare_testing', config('database.connections.mysql.database'));
        
        // Seed data LMS
        $this->seed(StandardLmsSeeder::class);
    }

    /**
     * Buat user Nakes/Bidan beserta izin modul kms.
     * Menggantikan Role::factory() yang tidak tersedia di repositori ini.
     */
    private function userNakes(): User
    {
        $role = Role::firstOrCreate(['name_role' => 'Nakes / Bidan']);
        $role->permissions()->updateOrCreate(
            ['module_name' => 'kms'],
            ['name' => 'kms', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true],
        );

        return User::factory()->create(['role_id' => $role->id]);
    }

    /**
     * Test: Lookup data LMS per jenis kelamin dan umur menggunakan baris yang benar
     */
    public function test_lookup_lms_per_jenis_kelamin_dan_umur(): void
    {
        $service = app(ZScoreService::class);
        
        // Data untuk laki-laki 12 bulan
        $lmsL = StandardLms::where('indikator', 'BB_U')
            ->where('jenis_kelamin', 'L')
            ->where('acuan_nilai', 12)
            ->first();
        
        // Data untuk perempuan 12 bulan
        $lmsP = StandardLms::where('indikator', 'BB_U')
            ->where('jenis_kelamin', 'P')
            ->where('acuan_nilai', 12)
            ->first();
        
        $this->assertNotNull($lmsL);
        $this->assertNotNull($lmsP);
        
        // M (median) harus berbeda antara L dan P
        $this->assertNotEquals($lmsL->m, $lmsP->m);
        
        // Hitung z-score untuk berat yang sama
        $beratBadan = 9.0;
        
        $zL = $service->hitung('BB_U', 'L', $beratBadan, 12);
        $zP = $service->hitung('BB_U', 'P', $beratBadan, 12);
        
        // Hasil harus berbeda (karena standar L dan P berbeda)
        $this->assertNotNull($zL);
        $this->assertNotNull($zP);
        $this->assertNotEquals($zL, $zP);
    }

    /**
     * Test: Kewajaran terhadap kolom SD tabel WHO
     * 
     * Untuk setiap baris LMS, nilai sd_k yang dibulatkan 1 desimal
     * harus menghasilkan z ≈ k dengan toleransi tertentu
     */
    public function test_kewajaran_terhadap_kolom_sd(): void
    {
        $service = app(ZScoreService::class);
        
        // Ambil beberapa sample baris LMS
        $samples = StandardLms::where('indikator', 'BB_U')
            ->whereIn('acuan_nilai', [0, 12, 24, 48])
            ->get();
        
        $this->assertGreaterThan(0, $samples->count());
        
        foreach ($samples as $lms) {
            $m = (float) $lms->m;
            $s = (float) $lms->s;
            
            // Toleransi berdasarkan presisi kolom SD (1 desimal)
            $tolerance = 0.1 / ($m * $s);
            
            // Test untuk z = -3, -2, -1, 0, 1, 2, 3
            $zValues = [-3, -2, -1, 0, 1, 2, 3];
            
            foreach ($zValues as $k) {
                $colName = 'sd_' . ($k >= 0 ? 'plus' . $k : 'min' . abs($k));
                $sdValue = (float) $lms->$colName;
                
                // Hitung z dari nilai SD
                $zHasil = $service->hitung(
                    $lms->indikator,
                    $lms->jenis_kelamin,
                    $sdValue,
                    $lms->acuan_nilai
                );
                
                $this->assertNotNull($zHasil);
                
                // z harus mendekati k
                $this->assertEqualsWithDelta(
                    $k,
                    $zHasil,
                    $tolerance,
                    sprintf(
                        "%s %s umur %.0f, SD(%d) = %.1f: z = %.2f (expected ≈ %d)",
                        $lms->jenis_kelamin,
                        $lms->indikator,
                        $lms->acuan_nilai,
                        $k,
                        $sdValue,
                        $zHasil,
                        $k
                    )
                );
            }
        }
    }

    /**
     * Test: Usia di luar rentang 0-60 bulan menghasilkan null
     */
    public function test_usia_di_luar_rentang_menghasilkan_null(): void
    {
        $service = app(ZScoreService::class);
        
        // Usia 61 bulan (di luar rentang)
        $z = $service->hitung('BB_U', 'L', 15.0, 61);
        $this->assertNull($z);
        
        // Usia negatif (tidak valid)
        $z = $service->hitung('BB_U', 'L', 15.0, -1);
        $this->assertNull($z);
    }

    /**
     * Test: Indikator tanpa data (TB_U, BB_TB) menghasilkan null tanpa error
     */
    public function test_indikator_tanpa_data_menghasilkan_null(): void
    {
        $service = app(ZScoreService::class);
        
        // TB_U belum dimuat
        $z = $service->hitung('TB_U', 'L', 75.0, 12);
        $this->assertNull($z);
        
        // BB_TB belum dimuat
        $z = $service->hitung('BB_TB', 'P', 10.0, 85.5);
        $this->assertNull($z);
        
        // Tidak ada exception yang dilempar
        $this->assertTrue(true);
    }

    /**
     * Test: Interpolasi BB_PB/BB_TB
     * 
     * Insert baris LMS buatan dengan langkah 0.5 cm
     * dan pastikan nilai tengah terinterpolasi benar
     */
    public function test_interpolasi_bb_pb(): void
    {
        // Insert data LMS buatan untuk BB_PB dengan langkah 0.5 cm
        StandardLms::insert([
            [
                'indikator' => 'BB_PB',
                'jenis_kelamin' => 'L',
                'acuan_nilai' => 50.0,
                'l' => 0.1,
                'm' => 4.0,
                's' => 0.1,
                'sd_min3' => 3.0,
                'sd_min2' => 3.3,
                'sd_min1' => 3.6,
                'sd_0' => 4.0,
                'sd_plus1' => 4.4,
                'sd_plus2' => 4.9,
                'sd_plus3' => 5.4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'indikator' => 'BB_PB',
                'jenis_kelamin' => 'L',
                'acuan_nilai' => 51.0,
                'l' => 0.1,
                'm' => 4.2,
                's' => 0.1,
                'sd_min3' => 3.1,
                'sd_min2' => 3.5,
                'sd_min1' => 3.8,
                'sd_0' => 4.2,
                'sd_plus1' => 4.6,
                'sd_plus2' => 5.1,
                'sd_plus3' => 5.6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $service = app(ZScoreService::class);
        
        // Nilai tepat pada 50.0 cm
        $z50 = $service->hitung('BB_PB', 'L', 4.0, 50.0);
        $this->assertEqualsWithDelta(0.0, $z50, 0.01);
        
        // Nilai tepat pada 51.0 cm
        $z51 = $service->hitung('BB_PB', 'L', 4.2, 51.0);
        $this->assertEqualsWithDelta(0.0, $z51, 0.01);
        
        // Nilai tengah 50.5 cm harus terinterpolasi
        // M interpolasi = 4.0 + (4.2 - 4.0) * 0.5 = 4.1
        $zTengah = $service->hitung('BB_PB', 'L', 4.1, 50.5);
        $this->assertNotNull($zTengah);
        $this->assertEqualsWithDelta(0.0, $zTengah, 0.1);
        
        // Nilai di luar rentang (49.9 atau 51.1) menghasilkan null
        $zLuar = $service->hitung('BB_PB', 'L', 4.0, 49.9);
        $this->assertNull($zLuar);
        
        $zLuar = $service->hitung('BB_PB', 'L', 4.2, 51.1);
        $this->assertNull($zLuar);
    }

    /**
     * Test: Store pengukuran via HTTP mengisi z_score_bbu
     */
    public function test_store_pengukuran_http_mengisi_z_score(): void
    {
        // Setup data
        $user = $this->userNakes();
        $orangTua = OrangTua::factory()->create();
        $balita = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'jenis_kelamin' => 'L',
            'birth_date' => now()->subMonths(12),
        ]);
        
        $this->actingAs($user);
        
        // POST pengukuran
        $response = $this->post(route('kms.store'), [
            'balita_id' => $balita->id,
            'date' => now()->format('Y-m-d'),
            'berat_badan' => '9,6',
            'tinggi_badan' => '75,0',
            'lingkar_lengan_atas' => '13,5',
        ]);
        
        $response->assertRedirect(route('kms.index'));
        $response->assertSessionHas('success');
        
        // Cek data tersimpan
        $pengukuran = PengukuranKms::latest()->first();
        $this->assertNotNull($pengukuran);
        $this->assertEquals(9.6, $pengukuran->berat_badan);
        
        // z_score_bbu harus terhitung (2 desimal)
        $this->assertNotNull($pengukuran->z_score_bbu);
        $this->assertMatchesRegularExpression('/^-?\d+\.\d{2}$/', (string)$pengukuran->z_score_bbu);
        
        // KF-15: status gizi ditentukan otomatis dari z-score tersimpan
        $this->assertNotNull($pengukuran->getRawOriginal('status_gizi'));
    }

    /**
     * Test: Update berat badan mengubah z-score
     */
    public function test_update_berat_badan_mengubah_z_score(): void
    {
        $user = $this->userNakes();
        $orangTua = OrangTua::factory()->create();
        $balita = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'jenis_kelamin' => 'L',
            'birth_date' => now()->subMonths(12),
        ]);
        
        $pengukuran = PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'user_id' => $user->id,
            'date' => now(),
            'berat_badan' => 9.0,
            'tinggi_badan' => 75.0,
            'z_score_bbu' => null,
            'z_score_tbu' => null,
            'z_score_bbtb' => null,
        ]);
        
        $this->actingAs($user);
        
        // Update berat badan
        $response = $this->put(route('kms.update', $pengukuran), [
            'balita_id' => $balita->id,
            'date' => now()->format('Y-m-d'),
            'berat_badan' => '10,5',
            'tinggi_badan' => '75,0',
        ]);
        
        $response->assertRedirect(route('kms.show', $pengukuran));
        
        // Reload dan cek z-score berubah
        $pengukuran->refresh();
        $this->assertEquals(10.5, $pengukuran->berat_badan);
        $this->assertNotNull($pengukuran->z_score_bbu);
    }

    /**
     * Test: Request yang mencoba mengirim z_score_bbu sendiri tidak berpengaruh
     */
    public function test_request_tidak_bisa_override_z_score(): void
    {
        $user = $this->userNakes();
        $orangTua = OrangTua::factory()->create();
        $balita = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'jenis_kelamin' => 'L',
            'birth_date' => now()->subMonths(12),
        ]);
        
        $this->actingAs($user);
        
        // POST dengan z_score_bbu palsu
        $response = $this->post(route('kms.store'), [
            'balita_id' => $balita->id,
            'date' => now()->format('Y-m-d'),
            'berat_badan' => '9,6',
            'tinggi_badan' => '75,0',
            'z_score_bbu' => '99.99', // Nilai palsu
        ]);
        
        $pengukuran = PengukuranKms::latest()->first();
        
        // z_score_bbu harus dihitung dari service, bukan dari input
        $this->assertNotEquals(99.99, $pengukuran->z_score_bbu);
        $this->assertLessThan(10.0, abs($pengukuran->z_score_bbu ?? 0));
    }

    /**
     * Test: Kegagalan kalkulasi tidak menggagalkan penyimpanan
     */
    public function test_gagal_kalkulasi_tidak_gagalkan_penyimpanan(): void
    {
        $user = $this->userNakes();
        $orangTua = OrangTua::factory()->create();
        $balita = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'jenis_kelamin' => 'L',
            'birth_date' => now()->subMonths(12),
        ]);

        $this->actingAs($user);

        // Simulasikan kegagalan tak terduga pada ZScoreService
        $this->mock(ZScoreService::class, function ($mock) {
            $mock->shouldReceive('untukPengukuran')->andThrow(new \RuntimeException('boom'));
        });

        // Ekspektasi warning di log
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'Gagal menghitung z-score');
            });

        // POST pengukuran (kalkulasi z-score gagal)
        $response = $this->post(route('kms.store'), [
            'balita_id' => $balita->id,
            'date' => now()->format('Y-m-d'),
            'berat_badan' => '9,6',
            'tinggi_badan' => '75,0',
        ]);

        // Penyimpanan tetap berhasil
        $response->assertRedirect(route('kms.index'));

        $pengukuran = PengukuranKms::latest()->first();
        $this->assertNotNull($pengukuran);
        $this->assertEquals(9.6, $pengukuran->berat_badan);

        // z_score null karena kalkulasi gagal
        $this->assertNull($pengukuran->z_score_bbu);
    }

    /**
     * Test: Command kms:hitung-zscore dry-run tidak mengubah data
     */
    public function test_command_dry_run_tidak_mengubah_data(): void
    {
        $user = $this->userNakes();
        $orangTua = OrangTua::factory()->create();
        $balita = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'jenis_kelamin' => 'L',
            'birth_date' => now()->subMonths(12),
        ]);
        
        $pengukuran = PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'user_id' => $user->id,
            'date' => now(),
            'berat_badan' => 9.6,
            'tinggi_badan' => 75.0,
            'z_score_bbu' => 99.99, // Nilai dummy
        ]);
        
        // Jalankan command dry-run
        $this->artisan('kms:hitung-zscore')
            ->expectsOutputToContain('DRY-RUN (tidak mengubah database)')
            ->assertSuccessful();
        
        // Data tidak berubah
        $pengukuran->refresh();
        $this->assertEquals(99.99, $pengukuran->z_score_bbu);
    }

    /**
     * Test: Command dengan --apply mengubah data
     */
    public function test_command_apply_mengubah_data(): void
    {
        $user = $this->userNakes();
        $orangTua = OrangTua::factory()->create();
        $balita = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'jenis_kelamin' => 'L',
            'birth_date' => now()->subMonths(12),
        ]);
        
        $pengukuran = PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'user_id' => $user->id,
            'date' => now(),
            'berat_badan' => 9.6,
            'tinggi_badan' => 75.0,
            'z_score_bbu' => 99.99, // Nilai dummy
        ]);
        
        // Jalankan command apply dengan no-interaction
        $this->artisan('kms:hitung-zscore --apply --no-interaction')
            ->assertSuccessful();
        
        // Data berubah
        $pengukuran->refresh();
        $this->assertNotEquals(99.99, $pengukuran->z_score_bbu);
        $this->assertNotNull($pengukuran->z_score_bbu);
        $this->assertLessThan(10.0, abs($pengukuran->z_score_bbu));
    }

    /**
     * Test: Command dengan --balita membatasi pada satu balita
     */
    public function test_command_batasi_satu_balita(): void
    {
        $user = $this->userNakes();
        $orangTua = OrangTua::factory()->create();
        
        $balita1 = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'jenis_kelamin' => 'L',
            'birth_date' => now()->subMonths(12),
        ]);
        
        $balita2 = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'jenis_kelamin' => 'P',
            'birth_date' => now()->subMonths(12),
        ]);
        
        $pengukuran1 = PengukuranKms::factory()->create([
            'balita_id' => $balita1->id,
            'user_id' => $user->id,
            'date' => now(),
            'berat_badan' => 9.6,
            'tinggi_badan' => 75.0,
            'z_score_bbu' => 99.99,
        ]);
        
        $pengukuran2 = PengukuranKms::factory()->create([
            'balita_id' => $balita2->id,
            'user_id' => $user->id,
            'date' => now(),
            'berat_badan' => 9.0,
            'tinggi_badan' => 74.0,
            'z_score_bbu' => 88.88,
        ]);
        
        // Jalankan command hanya untuk balita1
        $this->artisan("kms:hitung-zscore --apply --balita={$balita1->id} --no-interaction")
            ->expectsOutput("Filter: Balita ID = {$balita1->id}")
            ->assertSuccessful();
        
        // Hanya pengukuran1 yang berubah
        $pengukuran1->refresh();
        $pengukuran2->refresh();
        
        $this->assertNotEquals(99.99, $pengukuran1->z_score_bbu);
        $this->assertEquals(88.88, $pengukuran2->z_score_bbu); // Tidak berubah
    }

    /**
     * Test: Kinerja - 5000 perhitungan selesai dalam < 1 detik
     */
    public function test_kinerja_5000_perhitungan(): void
    {
        $service = app(ZScoreService::class);
        $service->muatSemua();
        
        $start = microtime(true);
        
        for ($i = 0; $i < 5000; $i++) {
            $umur = rand(0, 60);
            $jenisKelamin = rand(0, 1) === 0 ? 'L' : 'P';
            $beratBadan = rand(30, 200) / 10; // 3.0 - 20.0 kg
            
            $service->hitung('BB_U', $jenisKelamin, $beratBadan, $umur);
        }
        
        $duration = microtime(true) - $start;
        
        // Harus selesai dalam < 1 detik
        $this->assertLessThan(1.0, $duration, sprintf('5000 perhitungan memakan waktu %.3f detik', $duration));
        
        echo sprintf("\n✓ 5000 perhitungan selesai dalam %.3f detik\n", $duration);
    }

    /**
     * Test: Jumlah query - satu store pengukuran hanya <= 3 query ke standard_lms
     */
    public function test_jumlah_query_store_pengukuran(): void
    {
        $user = $this->userNakes();
        $orangTua = OrangTua::factory()->create();
        $balita = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'jenis_kelamin' => 'L',
            'birth_date' => now()->subMonths(12),
        ]);
        
        $this->actingAs($user);
        
        DB::enableQueryLog();
        DB::flushQueryLog();
        
        // POST pengukuran
        $this->post(route('kms.store'), [
            'balita_id' => $balita->id,
            'date' => now()->format('Y-m-d'),
            'berat_badan' => '9,6',
            'tinggi_badan' => '75,0',
        ]);
        
        $queries = DB::getQueryLog();
        
        // Filter query ke standard_lms
        $lmsQueries = array_filter($queries, function ($q) {
            return str_contains($q['query'], 'standard_lms');
        });
        
        $lmsQueryCount = count($lmsQueries);
        
        // Maksimal 3 query (BB_U, TB_U, BB_TB)
        $this->assertLessThanOrEqual(3, $lmsQueryCount, sprintf('Query ke standard_lms: %d', $lmsQueryCount));
        
        echo sprintf("\n✓ Store pengukuran: %d query ke standard_lms\n", $lmsQueryCount);
    }

    /**
     * Test: Waktu HTTP store pengukuran < 2000 ms
     */
    public function test_waktu_http_store_pengukuran(): void
    {
        $user = $this->userNakes();
        $orangTua = OrangTua::factory()->create();
        $balita = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'jenis_kelamin' => 'L',
            'birth_date' => now()->subMonths(12),
        ]);
        
        $this->actingAs($user);
        
        $start = microtime(true);
        
        $this->post(route('kms.store'), [
            'balita_id' => $balita->id,
            'date' => now()->format('Y-m-d'),
            'berat_badan' => '9,6',
            'tinggi_badan' => '75,0',
        ]);
        
        $duration = (microtime(true) - $start) * 1000; // ms
        
        // Harus < 2000 ms
        $this->assertLessThan(2000, $duration, sprintf('HTTP store memakan waktu %.0f ms', $duration));
        
        echo sprintf("\n✓ HTTP store pengukuran: %.0f ms\n", $duration);
    }
}
