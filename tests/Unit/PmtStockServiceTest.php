<?php

namespace Tests\Unit;

use App\Models\BarangPmt;
use App\Models\StokGudang;
use App\Services\PmtStockService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use Tests\TestCase;

class PmtStockServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected PmtStockService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PmtStockService();
    }

    protected function createDummyBarang(int $minBatasStok = 50): BarangPmt
    {
        return BarangPmt::create([
            'nama' => 'Biskuit FEFO Unit Test',
            'kategori' => 'Makanan Jadi',
            'unit_type' => 'kotak',
            'min_batas_stok' => $minBatasStok,
            'energi_kcal' => 450,
            'protein' => 10,
            'lemak' => 12,
            'karbohidrat' => 65,
        ]);
    }

    public function test_get_fefo_queue_orders_batches_by_earliest_expiry_date_and_excludes_expired_or_empty(): void
    {
        $barang = $this->createDummyBarang();

        // Batch 1: Expired (tidak boleh masuk antrean FEFO aktif)
        StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-EXPIRED',
            'date_in' => Carbon::today()->subMonths(4),
            'exp_date' => Carbon::today()->subDays(2),
            'stock_qty' => 40,
        ]);

        // Batch 2: Habis (tidak boleh masuk antrean FEFO aktif)
        StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-EMPTY',
            'date_in' => Carbon::today()->subMonths(2),
            'exp_date' => Carbon::today()->addDays(20),
            'stock_qty' => 0,
        ]);

        // Batch 3: ED masih jauh (60 hari)
        $batchLater = StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-LATER',
            'date_in' => Carbon::today()->subDays(10),
            'exp_date' => Carbon::today()->addDays(60),
            'stock_qty' => 100,
        ]);

        // Batch 4: ED paling dekat (15 hari) -> Harus menjadi urutan pertama FEFO
        $batchEarliest = StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-EARLIEST',
            'date_in' => Carbon::today()->subDays(5),
            'exp_date' => Carbon::today()->addDays(15),
            'stock_qty' => 60,
        ]);

        $queue = $this->service->getFefoQueueForBarang($barang->id);

        $this->assertCount(2, $queue);
        $this->assertEquals($batchEarliest->id, $queue->first()->id);
        $this->assertEquals($batchLater->id, $queue->last()->id);
    }

    public function test_determine_fefo_status_assigns_prioritas_antrean_and_aman_accurately(): void
    {
        $barang = $this->createDummyBarang();

        $b1 = StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-01',
            'date_in' => Carbon::today()->subDays(10),
            'exp_date' => Carbon::today()->addDays(15),
            'stock_qty' => 50,
        ]);

        $b2 = StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-02',
            'date_in' => Carbon::today()->subDays(8),
            'exp_date' => Carbon::today()->addDays(45),
            'stock_qty' => 80,
        ]);

        $b3 = StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-03',
            'date_in' => Carbon::today()->subDays(3),
            'exp_date' => Carbon::today()->addDays(120),
            'stock_qty' => 120,
        ]);

        $queue = $this->service->getFefoQueueForBarang($barang->id);

        $this->assertEquals('prioritas_fefo', $this->service->determineFefoStatus($b1, $queue)['code']);
        $this->assertEquals('antrean_berikutnya', $this->service->determineFefoStatus($b2, $queue)['code']);
        $this->assertEquals('stok_aman', $this->service->determineFefoStatus($b3, $queue)['code']);
    }

    public function test_allocate_and_deduct_stock_fefo_consumes_earliest_batch_first_and_spills_over(): void
    {
        $barang = $this->createDummyBarang();

        $batch1 = StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-FEFO-1',
            'date_in' => Carbon::today()->subDays(20),
            'exp_date' => Carbon::today()->addDays(10),
            'stock_qty' => 30,
        ]);

        $batch2 = StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-FEFO-2',
            'date_in' => Carbon::today()->subDays(10),
            'exp_date' => Carbon::today()->addDays(50),
            'stock_qty' => 70,
        ]);

        // Keluarkan 45 kotak -> 30 habis dari batch1, 15 diambil dari batch2
        $allocations = $this->service->deductStockFefo($barang->id, 45);

        $this->assertCount(2, $allocations);
        $this->assertEquals('BTC-FEFO-1', $allocations[0]['no_batch']);
        $this->assertEquals(30.0, $allocations[0]['allocated_qty']);
        $this->assertEquals(0.0, $allocations[0]['remaining_qty']);

        $this->assertEquals('BTC-FEFO-2', $allocations[1]['no_batch']);
        $this->assertEquals(15.0, $allocations[1]['allocated_qty']);
        $this->assertEquals(55.0, $allocations[1]['remaining_qty']);

        // Verifikasi saldo otomatis di database (KF-29)
        $batch1->refresh();
        $batch2->refresh();
        $this->assertEquals(0.0, (float) $batch1->stock_qty);
        $this->assertEquals(55.0, (float) $batch2->stock_qty);
        $this->assertEquals(55.0, $this->service->getTotalSaldoBarang($barang->id));
    }

    public function test_deduct_stock_fefo_throws_exception_when_stock_is_insufficient(): void
    {
        $barang = $this->createDummyBarang();

        StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-LIMIT',
            'date_in' => Carbon::today()->subDays(5),
            'exp_date' => Carbon::today()->addDays(30),
            'stock_qty' => 25,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->service->deductStockFefo($barang->id, 40);
    }

    public function test_critical_stock_and_near_expiry_indicators_detect_thresholds_accurately(): void
    {
        $barang = $this->createDummyBarang(50);

        $nearExpiryCriticalBatch = StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-WARN',
            'date_in' => Carbon::today()->subDays(10),
            'exp_date' => Carbon::today()->addDays(20), // <= 30 hari (hampir ED)
            'stock_qty' => 45,                          // <= 50 (stok kritis)
        ]);

        $safeBatch = StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-SAFE',
            'date_in' => Carbon::today()->subDays(5),
            'exp_date' => Carbon::today()->addDays(90), // > 30 hari (aman)
            'stock_qty' => 120,                         // > 50 (aman)
        ]);

        $nearExpiryCriticalBatch->load('barang');
        $safeBatch->load('barang');

        $this->assertTrue($this->service->isNearExpiry($nearExpiryCriticalBatch));
        $this->assertTrue($this->service->isCriticalStock($nearExpiryCriticalBatch));

        $this->assertFalse($this->service->isNearExpiry($safeBatch));
        $this->assertFalse($this->service->isCriticalStock($safeBatch));
    }
}
