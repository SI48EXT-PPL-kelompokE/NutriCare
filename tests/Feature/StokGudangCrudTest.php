<?php

namespace Tests\Feature;

use App\Models\BarangPmt;
use App\Models\StokGudang;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StokGudangCrudTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->userWithPermissions('Admin / Kepala Puskesmas', [
            'stok_gudang' => ['can_view', 'can_create', 'can_edit', 'can_delete'],
            'paket_pmt' => ['can_view'],
        ]));
    }

    public function test_index_page_loads_and_displays_kpi_cards_and_fefo_badges(): void
    {
        $response = $this->get(route('pmt.stok.index'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Stok Gudang');
        $response->assertSee('Total Stok PMT Tersedia');
        $response->assertSee('Mendekati Kedaluwarsa');
        $response->assertSee('Prioritas FEFO');
    }

    public function test_store_creates_new_batch_and_updates_automatic_saldo(): void
    {
        $barang = BarangPmt::first();

        $postData = [
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-TEST-202611',
            'date_in' => Carbon::today()->toDateString(),
            'exp_date' => Carbon::today()->addMonths(6)->toDateString(),
            'stock_qty' => 250,
        ];

        $response = $this->post(route('pmt.stok.store'), $postData);

        $response->assertRedirect(route('pmt.stok.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('stok_gudang', [
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-TEST-202611',
            'stock_qty' => 250,
        ]);
    }

    public function test_store_validates_duplicate_batch_and_invalid_expiry_date(): void
    {
        $barang = BarangPmt::first();

        StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-DUP-001',
            'date_in' => Carbon::today()->subDays(10),
            'exp_date' => Carbon::today()->addMonths(3),
            'stock_qty' => 100,
        ]);

        // Uji duplikat nomor batch pada barang yang sama dan exp_date <= date_in
        $response = $this->post(route('pmt.stok.store'), [
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-DUP-001',
            'date_in' => Carbon::today()->toDateString(),
            'exp_date' => Carbon::today()->subDay()->toDateString(),
            'stock_qty' => 50,
        ]);

        $response->assertSessionHasErrors(['no_batch', 'exp_date']);
    }

    public function test_update_modifies_batch_details_and_recalculates_saldo(): void
    {
        $barang = BarangPmt::first();

        $stok = StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-UPD-OLD',
            'date_in' => Carbon::today()->subDays(15),
            'exp_date' => Carbon::today()->addMonths(4),
            'stock_qty' => 80,
        ]);

        $response = $this->put(route('pmt.stok.update', $stok), [
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-UPD-NEW',
            'date_in' => Carbon::today()->subDays(10)->toDateString(),
            'exp_date' => Carbon::today()->addMonths(5)->toDateString(),
            'stock_qty' => 175,
        ]);

        $response->assertRedirect(route('pmt.stok.index'));
        $response->assertSessionHas('success');

        $stok->refresh();
        $this->assertEquals('BTC-UPD-NEW', $stok->no_batch);
        $this->assertEquals(175.0, (float) $stok->stock_qty);
    }

    public function test_destroy_deletes_unused_batch(): void
    {
        $barang = BarangPmt::first();

        $stok = StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-DEL-001',
            'date_in' => Carbon::today()->subDays(5),
            'exp_date' => Carbon::today()->addMonths(3),
            'stock_qty' => 60,
        ]);

        $response = $this->delete(route('pmt.stok.destroy', $stok));

        $response->assertRedirect(route('pmt.stok.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('stok_gudang', ['id' => $stok->id]);
    }

    public function test_keluarkan_fefo_deducts_from_earliest_expiring_batches_automatically(): void
    {
        $barang = BarangPmt::create([
            'nama' => 'Susu Formula FEFO Test',
            'kategori' => 'Susu',
            'unit_type' => 'kotak',
            'min_batas_stok' => 20,
            'energi_kcal' => 500,
            'protein' => 20,
            'lemak' => 20,
            'karbohidrat' => 50,
        ]);

        $batchAwal = StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-FEFO-AWAL',
            'date_in' => Carbon::today()->subDays(20),
            'exp_date' => Carbon::today()->addDays(14),
            'stock_qty' => 40,
        ]);

        $batchAkhir = StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-FEFO-AKHIR',
            'date_in' => Carbon::today()->subDays(10),
            'exp_date' => Carbon::today()->addDays(90),
            'stock_qty' => 100,
        ]);

        $response = $this->post(route('pmt.stok.keluarkan-fefo'), [
            'barang_id' => $barang->id,
            'jumlah_keluar' => 55,
        ]);

        $response->assertRedirect(route('pmt.stok.index'));
        $response->assertSessionHas('success');

        $batchAwal->refresh();
        $batchAkhir->refresh();

        $this->assertEquals(0.0, (float) $batchAwal->stock_qty);
        $this->assertEquals(85.0, (float) $batchAkhir->stock_qty);
    }

    public function test_preview_fefo_endpoint_returns_json_allocation_plan(): void
    {
        $barang = BarangPmt::create([
            'nama' => 'Bubur Bayi Preview FEFO',
            'kategori' => 'Makanan Jadi',
            'unit_type' => 'sachet',
            'min_batas_stok' => 30,
            'energi_kcal' => 350,
            'protein' => 12,
            'lemak' => 8,
            'karbohidrat' => 55,
        ]);

        StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BTC-PREV-01',
            'date_in' => Carbon::today()->subDays(5),
            'exp_date' => Carbon::today()->addDays(25),
            'stock_qty' => 50,
        ]);

        $response = $this->postJson(route('pmt.stok.preview-fefo'), [
            'barang_id' => $barang->id,
            'jumlah_keluar' => 20,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'saldo_tersedia' => 50,
            'saldo_setelah' => 30,
        ]);
    }

    public function test_scan_batch_endpoint_finds_batch_by_barcode_code(): void
    {
        $barang = BarangPmt::first();

        StokGudang::create([
            'barang_id' => $barang->id,
            'no_batch' => 'BARCODE-998877',
            'date_in' => Carbon::today()->subDays(3),
            'exp_date' => Carbon::today()->addMonths(4),
            'stock_qty' => 90,
        ]);

        $response = $this->getJson(route('pmt.stok.scan-batch', ['code' => 'BARCODE-998877']));

        $response->assertStatus(200);
        $response->assertJson([
            'found' => true,
            'batch' => [
                'no_batch' => 'BARCODE-998877',
                'stock_qty' => 90,
            ],
        ]);
    }
}
