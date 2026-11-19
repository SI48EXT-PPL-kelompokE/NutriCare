<?php

namespace Tests\Unit;

use App\Models\Balita;
use App\Models\OrangTua;
use App\Models\PengukuranKms;
use App\Models\Role;
use App\Models\User;
use App\Services\GrowthChartService;
use Carbon\Carbon;
use Database\Seeders\StandardLmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit test GrowthChartService (KF-16).
 *
 * Memakai database nutricare_testing (lihat phpunit.xml); tidak menyentuh
 * database dev nutricare.
 */
class GrowthChartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertEquals('nutricare_testing', config('database.connections.mysql.database'));
        $this->seed(StandardLmsSeeder::class);
    }

    private function service(): GrowthChartService
    {
        return new GrowthChartService();
    }

    private function petugas(): User
    {
        $role = Role::firstOrCreate(['name_role' => 'Nakes / Bidan']);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function balita(string $jenisKelamin = 'L', string $lahir = '2025-06-01'): Balita
    {
        $orangTua = OrangTua::factory()->create();

        return Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'jenis_kelamin' => $jenisKelamin,
            'birth_date' => $lahir,
        ]);
    }

    private function ukur(
        Balita $balita,
        User $petugas,
        string $tanggal,
        float $berat,
        float $tinggi,
        ?float $zBbu = null,
        ?float $zTbu = null,
        ?float $zBbtb = null
    ): PengukuranKms {
        return PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'user_id' => $petugas->id,
            'date' => $tanggal,
            'berat_badan' => $berat,
            'tinggi_badan' => $tinggi,
            'z_score_bbu' => $zBbu,
            'z_score_tbu' => $zTbu,
            'z_score_bbtb' => $zBbtb,
            'status_gizi' => null,
        ]);
    }

    public function test_kurva_median_dan_sd_konsisten_dengan_csv(): void
    {
        // BB/U laki-laki 12 bulan: l=0.0644, m=9.6479, s=0.10925
        $median = GrowthChartService::nilaiSD(0.0644, 9.6479, 0.10925, 0);
        $this->assertEqualsWithDelta(9.6479, $median, 0.0001);
        $this->assertSame(9.65, round($median, 2));

        // Bandingkan hasil L,M,S dengan kolom sd_min2 dan sd_min3 dari CSV (toleransi 0,05 kg).
        $min2 = round(GrowthChartService::nilaiSD(0.0644, 9.6479, 0.10925, -2), 2);
        $min3 = round(GrowthChartService::nilaiSD(0.0644, 9.6479, 0.10925, -3), 2);

        $this->assertEqualsWithDelta(7.7, $min2, 0.05);
        $this->assertEqualsWithDelta(6.9, $min3, 0.05);
    }

    public function test_kurva_l_nol_memakai_rumus_eksponensial(): void
    {
        // L = 0 -> SD(k) = M * exp(S*k)
        $this->assertEqualsWithDelta(12.214, GrowthChartService::nilaiSD(0.0, 10.0, 0.1, 2), 0.001);
        $this->assertEqualsWithDelta(8.187, GrowthChartService::nilaiSD(0.0, 10.0, 0.1, -2), 0.001);
    }

    public function test_usia_desimal_dihitung_dari_selisih_hari(): void
    {
        $lahir = Carbon::parse('2026-01-01');
        $ukur = Carbon::parse('2026-02-01'); // 31 hari

        // 31 / 30,4375 = 1,0185 -> 1,02
        $this->assertSame(1.02, GrowthChartService::usiaDesimal($lahir, $ukur));
    }

    public function test_rentang_sumbu_x_minimum_maksimum_kelipatan_enam(): void
    {
        $this->assertSame([0, 24], GrowthChartService::rentangSumbuUsia(0));
        $this->assertSame([0, 36], GrowthChartService::rentangSumbuUsia(30));
        $this->assertSame([0, 60], GrowthChartService::rentangSumbuUsia(58));

        [$min, $maks] = GrowthChartService::rentangSumbuUsia(47.5);

        $this->assertSame(0, $min);
        $this->assertSame(0, $maks % 6);
        $this->assertGreaterThanOrEqual(24, $maks);
        $this->assertLessThanOrEqual(60, $maks);
    }

    public function test_nol_pengukuran(): void
    {
        $balita = $this->balita();

        $grafik = $this->service()->bangun($balita);

        $this->assertSame(0, $grafik['jumlah_pengukuran']);
        $this->assertSame([], $grafik['indikator']['bb_u']['titik']);
        $this->assertNull($grafik['ringkasan']['bb_u_terkini']['nilai_label']);
        $this->assertNull($grafik['ringkasan']['tb_u_terkini']['nilai_label']);
        $this->assertSame('-', $grafik['ringkasan']['kenaikan_terakhir']['label']);
        $this->assertNull($grafik['ringkasan']['status']['nilai']);
        $this->assertFalse($grafik['ringkasan']['status']['lengkap']);
        $this->assertSame('Belum dapat dinilai', $grafik['ringkasan']['status']['stunting_keterangan']);
    }

    public function test_satu_pengukuran(): void
    {
        $balita = $this->balita('L', '2025-06-01');
        $petugas = $this->petugas();
        $this->ukur($balita, $petugas, '2026-06-01', 9.0, 75.0, -0.5, null, null);

        $grafik = $this->service()->bangun($balita);

        $this->assertSame(1, $grafik['jumlah_pengukuran']);
        $this->assertCount(1, $grafik['indikator']['bb_u']['titik']);
        $this->assertTrue($grafik['indikator']['bb_u']['titik'][0]['terbaru']);
        // Satu pengukuran: tidak ada kenaikan.
        $this->assertSame('-', $grafik['ringkasan']['kenaikan_terakhir']['label']);
    }

    public function test_banyak_pengukuran_dan_kenaikan_terakhir(): void
    {
        $balita = $this->balita('L', '2025-06-01');
        $petugas = $this->petugas();
        $this->ukur($balita, $petugas, '2026-01-10', 8.9, 74.0, -0.5, null, null);
        $this->ukur($balita, $petugas, '2026-03-10', 9.3, 76.0, -0.4, null, null);

        $grafik = $this->service()->bangun($balita);

        $this->assertSame(2, $grafik['jumlah_pengukuran']);
        $this->assertCount(2, $grafik['indikator']['bb_u']['titik']);

        $kenaikan = $grafik['ringkasan']['kenaikan_terakhir'];
        $this->assertSame(400, $kenaikan['gram']);
        $this->assertSame(2, $kenaikan['bulan']);
        $this->assertSame('+400 g dalam 2 bln', $kenaikan['label']);
    }

    public function test_indikator_tanpa_data_acuan_kurva_null_titik_tetap_ada(): void
    {
        $balita = $this->balita('L', '2024-01-01');
        $petugas = $this->petugas();
        $this->ukur($balita, $petugas, '2026-01-01', 12.0, 88.0, -0.5, -0.3, -0.4);

        $grafik = $this->service()->bangun($balita);

        // BB_U tersedia di standard_lms.
        $this->assertNotNull($grafik['indikator']['bb_u']['kurva']);

        // TB_U belum ada di standard_lms.
        $this->assertNull($grafik['indikator']['tb_u']['kurva']);
        $this->assertSame(
            'Kurva acuan WHO untuk indikator ini belum tersedia.',
            $grafik['indikator']['tb_u']['catatan_kurva']
        );
        $this->assertCount(1, $grafik['indikator']['tb_u']['titik']);

        // BB/TB juga belum ada data acuan (BB_PB/BB_TB).
        $this->assertNull($grafik['indikator']['bb_tb']['kurva']);
        $this->assertNotNull($grafik['indikator']['bb_tb']['catatan_kurva']);
    }

    public function test_ringkasan_memakai_z_tersimpan(): void
    {
        $balita = $this->balita('L', '2024-01-01');
        $petugas = $this->petugas();
        $this->ukur($balita, $petugas, '2026-01-01', 12.1, 90.0, -0.42, null, null);

        $r = $this->service()->bangun($balita)['ringkasan'];

        $this->assertSame(12.1, $r['bb_u_terkini']['nilai']);
        $this->assertSame('12,1', $r['bb_u_terkini']['nilai_label']);
        $this->assertSame(-0.42, $r['bb_u_terkini']['z']);
        $this->assertSame('-0,42 SD', $r['bb_u_terkini']['z_label']);
        $this->assertSame('Berat Badan Normal', $r['bb_u_terkini']['kategori']);
    }

    public function test_ringkasan_z_null_dan_penilaian_sebagian(): void
    {
        $balita = $this->balita('L', '2024-01-01');
        $petugas = $this->petugas();
        // Z-score tidak tersedia (mis. tabel LMS belum ada) -> kategori null, lengkap false.
        $this->ukur($balita, $petugas, '2026-01-01', 12.0, 88.0, null, null, null);

        $r = $this->service()->bangun($balita)['ringkasan'];

        $this->assertSame('12,0', $r['bb_u_terkini']['nilai_label']);
        $this->assertNull($r['bb_u_terkini']['z']);
        $this->assertNull($r['bb_u_terkini']['z_label']);
        $this->assertNull($r['bb_u_terkini']['kategori']);
        $this->assertFalse($r['status']['lengkap']);
        $this->assertFalse($r['status']['indeks_tersedia']['tb_u']);
        $this->assertSame('Belum dapat dinilai', $r['status']['stunting_keterangan']);
    }

    public function test_payload_tidak_memuat_nik(): void
    {
        $orangTua = OrangTua::factory()->create(['nik' => '3271234567890001']);
        $balita = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'jenis_kelamin' => 'L',
            'birth_date' => '2024-01-01',
        ]);
        $petugas = $this->petugas();
        $this->ukur($balita, $petugas, '2026-01-01', 12.0, 88.0, -0.5, null, null);

        $grafik = $this->service()->bangun($balita);
        $json = json_encode($grafik);

        $this->assertArrayNotHasKey('nik', $grafik['balita']);
        $this->assertStringNotContainsString('3271234567890001', $json);
    }
}
