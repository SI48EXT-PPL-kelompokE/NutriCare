<?php

namespace Tests\Feature;

use App\Models\BarangPmt;
use App\Models\DistribusiPmt;
use App\Models\KomposisiPaketPmt;
use App\Models\PaketPmt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PaketPmtCrudTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_index_page_loads_successfully_and_displays_seeded_packages(): void
    {
        $response = $this->get(route('pmt.paket.index'));

        $response->assertStatus(200);
        $response->assertSee('Master Paket PMT');
        $response->assertSee('PMT Pemulihan 6-23 Bulan');
        $response->assertSee('Tambah Paket Baru');
    }

    public function test_create_page_loads_successfully(): void
    {
        $response = $this->get(route('pmt.paket.create'));

        $response->assertStatus(200);
        $response->assertSee('Form Pembuatan Paket');
        $response->assertSee('Estimasi Akumulasi Nilai Nutrisi');
    }

    public function test_store_creates_new_package_with_accurate_auto_calculated_nutrition(): void
    {
        $barang1 = BarangPmt::first();
        $barang2 = BarangPmt::skip(1)->first();

        $postData = [
            'nama_paket_pmt' => 'Paket Feature Test Baru',
            'deskripsi' => 'Paket untuk pengujian otomatis feature test',
            'min_age_target' => 6,
            'max_age_target' => 24,
            'unit_type' => 'paket',
            'status' => 1,
            'komposisi' => [
                [
                    'barang_id' => $barang1->id,
                    'jumlah' => 100, // faktor = 1.0
                    'unit_type' => 'gram',
                ],
                [
                    'barang_id' => $barang2->id,
                    'jumlah' => 200, // faktor = 2.0
                    'unit_type' => 'gram',
                ],
            ],
        ];

        $expectedEnergi = round(($barang1->energi_kcal * 1.0) + ($barang2->energi_kcal * 2.0), 2);
        $expectedProtein = round(($barang1->protein * 1.0) + ($barang2->protein * 2.0), 2);

        $response = $this->post(route('pmt.paket.store'), $postData);

        $response->assertRedirect(route('pmt.paket.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('paket_pmt', [
            'nama_paket_pmt' => 'Paket Feature Test Baru',
            'min_age_target' => 6,
            'max_age_target' => 24,
            'total_energi_kcal' => $expectedEnergi,
            'total_protein' => $expectedProtein,
        ]);

        $createdPaket = PaketPmt::where('nama_paket_pmt', 'Paket Feature Test Baru')->first();
        $this->assertCount(2, $createdPaket->komposisiPaketPmt);
    }

    public function test_store_fails_when_target_age_is_less_than_6_months(): void
    {
        $barang = BarangPmt::first();

        $postData = [
            'nama_paket_pmt' => 'Paket Usia Dibawah 6 Bulan',
            'min_age_target' => 4, // Tidak valid! Standar MP-ASI minimal 6 bulan
            'max_age_target' => 12,
            'unit_type' => 'paket',
            'komposisi' => [
                [
                    'barang_id' => $barang->id,
                    'jumlah' => 100,
                    'unit_type' => 'gram',
                ],
            ],
        ];

        $response = $this->post(route('pmt.paket.store'), $postData);

        $response->assertSessionHasErrors(['min_age_target']);
        $this->assertDatabaseMissing('paket_pmt', [
            'nama_paket_pmt' => 'Paket Usia Dibawah 6 Bulan',
        ]);
    }

    public function test_store_fails_when_target_age_is_greater_than_59_months_or_inverted(): void
    {
        $barang = BarangPmt::first();

        // Uji usia maksimal melebihi 59 bulan
        $postDataOver = [
            'nama_paket_pmt' => 'Paket Usia Diatas 59 Bulan',
            'min_age_target' => 12,
            'max_age_target' => 65,
            'unit_type' => 'paket',
            'komposisi' => [
                [
                    'barang_id' => $barang->id,
                    'jumlah' => 100,
                    'unit_type' => 'gram',
                ],
            ],
        ];

        $responseOver = $this->post(route('pmt.paket.store'), $postDataOver);
        $responseOver->assertSessionHasErrors(['max_age_target']);

        // Uji min > max
        $postDataInverted = [
            'nama_paket_pmt' => 'Paket Usia Terbalik',
            'min_age_target' => 24,
            'max_age_target' => 12,
            'unit_type' => 'paket',
            'komposisi' => [
                [
                    'barang_id' => $barang->id,
                    'jumlah' => 100,
                    'unit_type' => 'gram',
                ],
            ],
        ];

        $responseInverted = $this->post(route('pmt.paket.store'), $postDataInverted);
        $responseInverted->assertSessionHasErrors(['max_age_target']);
    }

    public function test_store_fails_when_duplicate_barang_in_composition(): void
    {
        $barang = BarangPmt::first();

        $postData = [
            'nama_paket_pmt' => 'Paket Duplikat Bahan',
            'min_age_target' => 6,
            'max_age_target' => 24,
            'unit_type' => 'paket',
            'komposisi' => [
                [
                    'barang_id' => $barang->id,
                    'jumlah' => 100,
                    'unit_type' => 'gram',
                ],
                [
                    'barang_id' => $barang->id, // Duplikat barang yang sama!
                    'jumlah' => 50,
                    'unit_type' => 'gram',
                ],
            ],
        ];

        $response = $this->post(route('pmt.paket.store'), $postData);

        $response->assertSessionHasErrors(['komposisi.1.barang_id']);
    }

    public function test_show_page_displays_package_composition_and_macronutrients(): void
    {
        $paket = PaketPmt::with('komposisiPaketPmt.barang')->first();

        $response = $this->get(route('pmt.paket.show', $paket));

        $response->assertStatus(200);
        $response->assertSee($paket->nama_paket_pmt);
        $response->assertSee('Komposisi Bahan Makanan');
        $response->assertSee('Distribusi Makronutrien Paket');
    }

    public function test_edit_page_loads_existing_data(): void
    {
        $paket = PaketPmt::first();

        $response = $this->get(route('pmt.paket.edit', $paket));

        $response->assertStatus(200);
        $response->assertSee($paket->nama_paket_pmt);
        $response->assertSee('Simpan Perubahan Paket');
    }

    public function test_update_modifies_package_and_recalculates_nutrition(): void
    {
        $paket = PaketPmt::create([
            'nama_paket_pmt' => 'Paket Awal Sebelum Update',
            'min_age_target' => 6,
            'max_age_target' => 12,
            'unit_type' => 'paket',
            'status' => true,
            'total_energi_kcal' => 100,
            'total_protein' => 10,
            'total_lemak' => 5,
            'total_karbohidrat' => 15,
        ]);

        $barang = BarangPmt::first();

        $updateData = [
            'nama_paket_pmt' => 'Paket Setelah Diupdate',
            'min_age_target' => 12,
            'max_age_target' => 36,
            'unit_type' => 'porsi',
            'status' => 1,
            'komposisi' => [
                [
                    'barang_id' => $barang->id,
                    'jumlah' => 300, // faktor 3.0
                    'unit_type' => 'gram',
                ],
            ],
        ];

        $response = $this->put(route('pmt.paket.update', $paket), $updateData);

        $response->assertRedirect(route('pmt.paket.show', $paket));
        $response->assertSessionHas('success');

        $paket->refresh();
        $this->assertEquals('Paket Setelah Diupdate', $paket->nama_paket_pmt);
        $this->assertEquals(12, $paket->min_age_target);
        $this->assertEquals(36, $paket->max_age_target);
        $this->assertEquals('porsi', $paket->unit_type);
        $this->assertEquals(round($barang->energi_kcal * 3.0, 2), (float) $paket->total_energi_kcal);
    }

    public function test_toggle_status_flips_package_active_flag(): void
    {
        $paket = PaketPmt::create([
            'nama_paket_pmt' => 'Paket Toggle Test',
            'min_age_target' => 6,
            'max_age_target' => 24,
            'unit_type' => 'paket',
            'status' => true,
        ]);

        $response = $this->patch(route('pmt.paket.toggle-status', $paket));

        $response->assertSessionHas('success');
        $paket->refresh();
        $this->assertFalse((bool) $paket->status);

        // Toggle kembali
        $this->patch(route('pmt.paket.toggle-status', $paket));
        $paket->refresh();
        $this->assertTrue((bool) $paket->status);
    }

    public function test_destroy_deletes_package_without_distributions(): void
    {
        $paket = PaketPmt::create([
            'nama_paket_pmt' => 'Paket Siap Dihapus',
            'min_age_target' => 6,
            'max_age_target' => 12,
            'unit_type' => 'paket',
            'status' => true,
        ]);

        $response = $this->delete(route('pmt.paket.destroy', $paket));

        $response->assertRedirect(route('pmt.paket.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('paket_pmt', ['id' => $paket->id]);
    }

    public function test_destroy_deactivates_package_if_distributions_exist(): void
    {
        // Cari paket yang memiliki relasi ke distribusi_pmt
        $paketWithDistribusi = PaketPmt::whereHas('distribusiPmt')->first();
        $this->assertNotNull($paketWithDistribusi, 'Harus ada paket yang memiliki distribusi dari seeder');

        $paketWithDistribusi->update(['status' => true]);

        $response = $this->delete(route('pmt.paket.destroy', $paketWithDistribusi));

        $response->assertRedirect(route('pmt.paket.index'));
        $response->assertSessionHas('warning');

        // Pastikan record paket MASIH ADA di database, hanya statusnya diubah menjadi false
        $this->assertDatabaseHas('paket_pmt', [
            'id' => $paketWithDistribusi->id,
            'status' => false,
        ]);
    }

    public function test_calculate_preview_api_returns_json_totals(): void
    {
        $barang = BarangPmt::first();

        $response = $this->postJson(route('pmt.paket.calculate-preview'), [
            'komposisi' => [
                [
                    'barang_id' => $barang->id,
                    'jumlah' => 100,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_energi_kcal',
            'total_protein',
            'total_lemak',
            'total_karbohidrat',
        ]);
        $response->assertJson([
            'total_energi_kcal' => round((float) $barang->energi_kcal, 2),
        ]);
    }
}
