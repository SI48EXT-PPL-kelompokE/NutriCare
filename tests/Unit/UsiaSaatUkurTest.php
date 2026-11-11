<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PengukuranKms;
use App\Models\Balita;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class UsiaSaatUkurTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /**
     * Test: Tanggal ukur sama dengan tanggal lahir = 0 bulan
     */
    public function test_usia_0_bulan_saat_tanggal_ukur_sama_dengan_tanggal_lahir()
    {
        $balita = Balita::factory()->create([
            'birth_date' => Carbon::parse('2026-01-15'),
        ]);

        $pengukuran = PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'date' => Carbon::parse('2026-01-15'),
        ]);

        $this->assertEquals(0, $pengukuran->usia_bulan);
        $this->assertEquals('0 bulan', $pengukuran->usia_label);
    }

    /**
     * Test: Lahir 31 Jan, ukur 28 Feb = 0 bulan (belum 1 bulan penuh)
     */
    public function test_usia_0_bulan_lahir_31_jan_ukur_28_feb()
    {
        $balita = Balita::factory()->create([
            'birth_date' => Carbon::parse('2026-01-31'),
        ]);

        $pengukuran = PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'date' => Carbon::parse('2026-02-28'),
        ]);

        $this->assertEquals(0, $pengukuran->usia_bulan);
    }

    /**
     * Test: Lahir 15 Jan, ukur 15 Mar = 2 bulan (tepat 2 bulan kalender)
     */
    public function test_usia_2_bulan_lahir_15_jan_ukur_15_mar()
    {
        $balita = Balita::factory()->create([
            'birth_date' => Carbon::parse('2026-01-15'),
        ]);

        $pengukuran = PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'date' => Carbon::parse('2026-03-15'),
        ]);

        $this->assertEquals(2, $pengukuran->usia_bulan);
        $this->assertEquals('2 bulan', $pengukuran->usia_label);
    }

    /**
     * Test: Tepat 12 bulan
     */
    public function test_usia_12_bulan()
    {
        $balita = Balita::factory()->create([
            'birth_date' => Carbon::parse('2025-01-15'),
        ]);

        $pengukuran = PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'date' => Carbon::parse('2026-01-15'),
        ]);

        $this->assertEquals(12, $pengukuran->usia_bulan);
        $this->assertEquals('12 bulan (1 th)', $pengukuran->usia_label);
    }

    /**
     * Test: 14 bulan = "14 bulan (1 th 2 bln)"
     */
    public function test_usia_14_bulan()
    {
        $balita = Balita::factory()->create([
            'birth_date' => Carbon::parse('2024-12-01'),
        ]);

        $pengukuran = PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'date' => Carbon::parse('2026-02-01'),
        ]);

        $this->assertEquals(14, $pengukuran->usia_bulan);
        $this->assertEquals('14 bulan (1 th 2 bln)', $pengukuran->usia_label);
    }

    /**
     * Test: 23 bulan
     */
    public function test_usia_23_bulan()
    {
        $balita = Balita::factory()->create([
            'birth_date' => Carbon::parse('2024-03-15'),
        ]);

        $pengukuran = PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'date' => Carbon::parse('2026-02-15'),
        ]);

        $this->assertEquals(23, $pengukuran->usia_bulan);
        $this->assertEquals('23 bulan (1 th 11 bln)', $pengukuran->usia_label);
    }

    /**
     * Test: 24 bulan (tepat 2 tahun)
     */
    public function test_usia_24_bulan()
    {
        $balita = Balita::factory()->create([
            'birth_date' => Carbon::parse('2024-02-15'),
        ]);

        $pengukuran = PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'date' => Carbon::parse('2026-02-15'),
        ]);

        $this->assertEquals(24, $pengukuran->usia_bulan);
        $this->assertEquals('24 bulan (2 th)', $pengukuran->usia_label);
    }

    /**
     * Test: 59 bulan
     */
    public function test_usia_59_bulan()
    {
        $balita = Balita::factory()->create([
            'birth_date' => Carbon::parse('2021-03-15'),
        ]);

        $pengukuran = PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'date' => Carbon::parse('2026-02-15'),
        ]);

        $this->assertEquals(59, $pengukuran->usia_bulan);
        $this->assertEquals('59 bulan (4 th 11 bln)', $pengukuran->usia_label);
    }

    /**
     * Test: 60 bulan (batas maksimal)
     */
    public function test_usia_60_bulan()
    {
        $balita = Balita::factory()->create([
            'birth_date' => Carbon::parse('2021-02-15'),
        ]);

        $pengukuran = PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'date' => Carbon::parse('2026-02-15'),
        ]);

        $this->assertEquals(60, $pengukuran->usia_bulan);
        $this->assertEquals('60 bulan (5 th)', $pengukuran->usia_label);
    }

    /**
     * Test: Lahir 29 Feb tahun kabisat (2024), ukur 29 Feb 2025 (tidak kabisat)
     * Seharusnya 12 bulan
     */
    public function test_usia_lahir_29_feb_tahun_kabisat()
    {
        $balita = Balita::factory()->create([
            'birth_date' => Carbon::parse('2024-02-29'),
        ]);

        // 1 tahun kemudian (2025 bukan kabisat, gunakan 28 Feb)
        $pengukuran = PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'date' => Carbon::parse('2025-02-28'),
        ]);

        // 28 Feb 2025 masih belum 12 bulan penuh dari 29 Feb 2024
        $this->assertEquals(11, $pengukuran->usia_bulan);

        // 1 Maret 2025 sudah 12 bulan
        $pengukuran2 = PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'date' => Carbon::parse('2025-03-01'),
        ]);

        $this->assertEquals(12, $pengukuran2->usia_bulan);
    }
}
