<?php

namespace Tests\Unit;

use App\Models\BarangPmt;
use App\Models\KomposisiPaketPmt;
use App\Models\PaketPmt;
use App\Services\PmtNutritionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PmtNutritionServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected PmtNutritionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PmtNutritionService();
    }

    public function test_calculate_totals_with_empty_items_returns_zeros(): void
    {
        $result = $this->service->calculateTotals([]);

        $this->assertEquals(0.0, $result['total_energi_kcal']);
        $this->assertEquals(0.0, $result['total_protein']);
        $this->assertEquals(0.0, $result['total_lemak']);
        $this->assertEquals(0.0, $result['total_karbohidrat']);
    }

    public function test_calculate_totals_calculates_accurate_nutrition(): void
    {
        // Buat data barang dummy untuk test
        $barang = BarangPmt::create([
            'nama' => 'Biskuit Balita Test',
            'kategori' => 'Kudapan',
            'unit_type' => 'gram',
            'min_batas_stok' => 50,
            'energi_kcal' => 450.00,  // per 100g
            'protein' => 10.00,       // per 100g
            'lemak' => 15.00,         // per 100g
            'karbohidrat' => 60.00,   // per 100g
        ]);

        // Takaran: 200 gram (faktor = 2.0)
        $items = [
            [
                'barang_id' => $barang->id,
                'jumlah' => 200,
            ],
        ];

        $result = $this->service->calculateTotals($items);

        $this->assertEquals(900.00, $result['total_energi_kcal']);
        $this->assertEquals(20.00, $result['total_protein']);
        $this->assertEquals(30.00, $result['total_lemak']);
        $this->assertEquals(120.00, $result['total_karbohidrat']);
    }

    public function test_validate_age_target_catches_invalid_ranges(): void
    {
        // < 6 bulan (periode ASI eksklusif)
        $errorsUnder6 = $this->service->validateAgeTarget(5, 23);
        $this->assertNotEmpty($errorsUnder6);
        $this->assertStringContainsString('ASI eksklusif', $errorsUnder6[0]);

        // > 59 bulan (melebihi batas usia balita)
        $errorsOver59 = $this->service->validateAgeTarget(12, 65);
        $this->assertNotEmpty($errorsOver59);
        $this->assertStringContainsString('59 bulan', $errorsOver59[0]);

        // min > max
        $errorsInverted = $this->service->validateAgeTarget(24, 12);
        $this->assertNotEmpty($errorsInverted);

        // Valid range (6 - 23 bulan)
        $errorsValid = $this->service->validateAgeTarget(6, 23);
        $this->assertEmpty($errorsValid);
    }

    public function test_recalculate_for_paket_updates_model(): void
    {
        $barang = BarangPmt::create([
            'nama' => 'Telur Puyuh Test',
            'kategori' => 'Lauk',
            'unit_type' => 'gram',
            'min_batas_stok' => 20,
            'energi_kcal' => 150.00,
            'protein' => 12.00,
            'lemak' => 10.00,
            'karbohidrat' => 1.00,
        ]);

        $paket = PaketPmt::create([
            'nama_paket_pmt' => 'Paket Recalculate Test',
            'min_age_target' => 6,
            'max_age_target' => 23,
            'unit_type' => 'paket',
            'status' => true,
            'total_energi_kcal' => 0,
            'total_protein' => 0,
            'total_lemak' => 0,
            'total_karbohidrat' => 0,
        ]);

        KomposisiPaketPmt::create([
            'paket_id' => $paket->id,
            'barang_id' => $barang->id,
            'jumlah' => 100, // faktor = 1.0
            'unit_type' => 'gram',
        ]);

        $this->service->recalculateForPaket($paket);

        $paket->refresh();
        $this->assertEquals(150.00, (float) $paket->total_energi_kcal);
        $this->assertEquals(12.00, (float) $paket->total_protein);
        $this->assertEquals(10.00, (float) $paket->total_lemak);
        $this->assertEquals(1.00, (float) $paket->total_karbohidrat);
    }
}
