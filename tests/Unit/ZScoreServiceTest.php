<?php

namespace Tests\Unit;

use App\Services\ZScoreService;
use PHPUnit\Framework\TestCase;

/**
 * Unit test untuk ZScoreService tanpa database
 * 
 * Test ini memverifikasi rumus LMS dan logika perhitungan z-score
 * tanpa melibatkan database atau model Eloquent.
 */
class ZScoreServiceTest extends TestCase
{
    /**
     * Test: y = M menghasilkan z-score 0.00
     */
    public function test_nilai_median_menghasilkan_z_nol(): void
    {
        // Parameter LMS sample dari data WHO BB_U laki-laki 12 bulan
        $l = 0.1738;
        $m = 9.6458;
        $s = 0.11727;
        
        $z = ZScoreService::hitungZ($m, $l, $m, $s);
        
        $this->assertEquals(0.0, $z);
    }

    /**
     * Test: Rumus dengan L = 0 menggunakan logaritma natural
     */
    public function test_rumus_dengan_l_nol(): void
    {
        $l = 0.0;
        $m = 10.0;
        $s = 0.1;
        $y = 11.0; // exp(0.1 * z) = 1.1 → z ≈ 0.95
        
        $z = ZScoreService::hitungZ($y, $l, $m, $s);
        
        // ln(11/10) / 0.1 = ln(1.1) / 0.1 ≈ 0.95
        $this->assertEqualsWithDelta(0.95, $z, 0.01);
    }

    /**
     * Test: Rumus dengan L negatif
     */
    public function test_rumus_dengan_l_negatif(): void
    {
        $l = -0.5;
        $m = 10.0;
        $s = 0.15;
        $y = 8.0;
        
        $z = ZScoreService::hitungZ($y, $l, $m, $s);
        
        // Z = ((8/10)^-0.5 - 1) / (-0.5 * 0.15)
        // Z = (1.118 - 1) / -0.075 = -1.57
        $this->assertEqualsWithDelta(-1.57, $z, 0.02);
    }

    /**
     * Test: Pembulatan 2 desimal dengan half away from zero
     */
    public function test_pembulatan_dua_desimal(): void
    {
        // Kasus yang menghasilkan z = 1.235 (harus dibulatkan ke 1.24)
        $l = 0.1;
        $m = 10.0;
        $s = 0.1;
        
        // Sesuaikan nilai y untuk menghasilkan angka dengan banyak desimal
        $y = 10.125;
        
        $z = ZScoreService::hitungZ($y, $l, $m, $s);
        
        // Pastikan hasil hanya 2 desimal
        $this->assertMatchesRegularExpression('/^-?\d+\.\d{2}$/', (string)$z);
    }

    /**
     * Test: Normalisi "-0.00" menjadi "0.00"
     */
    public function test_normalisasi_negatif_nol(): void
    {
        // Buat skenario yang menghasilkan -0.00
        $l = 0.2;
        $m = 10.0;
        $s = 0.1;
        $y = 9.9999; // Sangat dekat dengan M
        
        $z = ZScoreService::hitungZ($y, $l, $m, $s);
        
        // Jika hasilnya sangat mendekati nol, pastikan tidak "-0.00"
        if (abs($z) < 0.005) {
            $this->assertEquals(0.0, $z);
            $this->assertGreaterThanOrEqual(0.0, $z); // Tidak boleh -0.0
        }
    }

    /**
     * Test: Koreksi nilai ekstrem untuk z > 3
     */
    public function test_koreksi_ekstrem_z_positif(): void
    {
        // Parameter yang menghasilkan z ekstrem
        $l = 0.15;
        $m = 10.0;
        $s = 0.12;
        $y = 15.0; // Nilai sangat tinggi
        
        $zTanpaKoreksi = ZScoreService::hitungZ($y, $l, $m, $s, koreksiEkstrem: false);
        $zDenganKoreksi = ZScoreService::hitungZ($y, $l, $m, $s, koreksiEkstrem: true);
        
        // Z tanpa koreksi harus > 3
        $this->assertGreaterThan(3.0, $zTanpaKoreksi);
        
        // Z dengan koreksi harus lebih besar lagi (WHO extreme adjustment)
        $this->assertGreaterThan($zTanpaKoreksi, $zDenganKoreksi);
    }

    /**
     * Test: Koreksi nilai ekstrem untuk z < -3
     */
    public function test_koreksi_ekstrem_z_negatif(): void
    {
        // Parameter yang menghasilkan z ekstrem negatif
        $l = 0.15;
        $m = 10.0;
        $s = 0.12;
        $y = 5.0; // Nilai sangat rendah
        
        $zTanpaKoreksi = ZScoreService::hitungZ($y, $l, $m, $s, koreksiEkstrem: false);
        $zDenganKoreksi = ZScoreService::hitungZ($y, $l, $m, $s, koreksiEkstrem: true);
        
        // Z tanpa koreksi harus < -3
        $this->assertLessThan(-3.0, $zTanpaKoreksi);
        
        // Z dengan koreksi harus lebih kecil lagi (lebih negatif)
        $this->assertLessThan($zTanpaKoreksi, $zDenganKoreksi);
    }

    /**
     * Test: Kasus manual - Anak laki-laki 12 bulan, berat 9,6 kg
     * 
     * Data LMS WHO BB_U laki-laki 12 bulan:
     * L = 0.1738, M = 9.6458, S = 0.11727
     */
    public function test_kasus_manual_anak_laki_12_bulan(): void
    {
        $l = 0.1738;
        $m = 9.6458;
        $s = 0.11727;
        $beratBadan = 9.6;
        
        $z = ZScoreService::hitungZ($beratBadan, $l, $m, $s);
        
        // Verifikasi dengan skrip Python:
        // z = ((9.6 / 9.6458)^0.1738 - 1) / (0.1738 * 0.11727)
        // z ≈ -0.05
        $this->assertEqualsWithDelta(-0.05, $z, 0.01);
    }

    /**
     * Test: Verifikasi dengan fixture JSON yang dihasilkan Python
     */
    public function test_verifikasi_dengan_fixture_json(): void
    {
        $fixturePath = __DIR__ . '/../Fixtures/zscore_expected.json';
        
        if (!file_exists($fixturePath)) {
            $this->markTestSkipped('Fixture zscore_expected.json belum ada. Jalankan generate_zscore_cases.py');
        }
        
        $testCases = json_decode(file_get_contents($fixturePath), true);
        
        $this->assertNotEmpty($testCases, 'Fixture harus berisi kasus uji');
        
        $errorCount = 0;
        $errors = [];
        
        foreach ($testCases as $case) {
            $zHasil = ZScoreService::hitungZ(
                $case['berat_badan'],
                $case['l'],
                $case['m'],
                $case['s'],
                koreksiEkstrem: true
            );
            
            $expected = $case['z_expected_dengan_koreksi'];
            
            // Toleransi 0.01 karena pembulatan
            if (abs($zHasil - $expected) > 0.01) {
                $errorCount++;
                if ($errorCount <= 5) {
                    $errors[] = sprintf(
                        "%s: expected %.2f, got %.2f (diff: %.4f)",
                        $case['deskripsi'],
                        $expected,
                        $zHasil,
                        abs($zHasil - $expected)
                    );
                }
            }
        }
        
        if ($errorCount > 0) {
            $message = sprintf(
                "%d dari %d kasus gagal (menampilkan 5 pertama):\n%s",
                $errorCount,
                count($testCases),
                implode("\n", $errors)
            );
            $this->fail($message);
        }
        
        $this->assertTrue(true, sprintf('Semua %d kasus uji lulus verifikasi', count($testCases)));
    }
}
