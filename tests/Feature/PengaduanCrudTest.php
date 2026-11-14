<?php

namespace Tests\Feature;

use App\Models\OrangTua;
use App\Models\Pengaduan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengaduanCrudTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Memastikan halaman daftar pengaduan warga memuat HTTP 200 dan elemen antarmuka mockup proposal.
     */
    public function test_index_page_loads_and_displays_complaints(): void
    {
        $response = $this->get(route('edukasi.pengaduan.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengaduan Warga');
        $response->assertSee('Catat Pengaduan');
        $response->assertSee('Ekspor Rekap');
        $response->assertSee('KATEGORI PENGADUAN');
        $response->assertSee('RINGKASAN PENGADUAN');
    }

    /**
     * SCRUM-189 [KF-52]: Memastikan pengaduan dapat dicatat dengan opsi anonim (identitas disembunyikan).
     */
    public function test_store_creates_anonymous_complaint(): void
    {
        $payload = [
            'kategori' => 'Distribusi PMT',
            'judul' => 'Keluhan Pembagian PMT Posyandu',
            'isi' => 'Pembagian PMT belum merata di meja 3.',
            'is_anonymous' => 1,
        ];

        $response = $this->post(route('edukasi.pengaduan.store'), $payload);

        $response->assertRedirect(route('edukasi.pengaduan.index'));
        $this->assertDatabaseHas('pengaduan', [
            'judul' => 'Keluhan Pembagian PMT Posyandu',
            'is_anonymous' => true,
            'orang_tua_id' => null,
            'status' => 'baru',
        ]);
    }

    /**
     * SCRUM-186 [KF-51]: Memastikan pengaduan dapat melampirkan foto bukti fisik yang tersimpan di storage.
     */
    public function test_store_uploads_proof_photo(): void
    {
        Storage::fake('public');

        $orangTua = OrangTua::first() ?? OrangTua::factory()->create();
        $file = UploadedFile::fake()->image('bukti_rusak.jpg', 600, 400)->size(500);

        $payload = [
            'kategori' => 'Layanan Posyandu',
            'judul' => 'Timbangan Bayi Rusak',
            'isi' => 'Timbangan di posyandu perlu dikalibrasi ulang.',
            'orang_tua_id' => $orangTua->id,
            'is_anonymous' => 0,
            'foto_bukti' => $file,
        ];

        $response = $this->post(route('edukasi.pengaduan.store'), $payload);

        $response->assertRedirect(route('edukasi.pengaduan.index'));
        $pengaduan = Pengaduan::where('judul', 'Timbangan Bayi Rusak')->first();

        $this->assertNotNull($pengaduan);
        $this->assertNotNull($pengaduan->foto_bukti_path);
        Storage::disk('public')->assertExists($pengaduan->foto_bukti_path);
    }

    /**
     * SCRUM-183 [KF-50]: Memastikan admin dapat menanggapi pengaduan dan mengubah status.
     */
    public function test_admin_can_respond_and_update_status(): void
    {
        $pengaduan = Pengaduan::create([
            'kategori' => 'Data KMS',
            'judul' => 'Data Berat Badan Belum Masuk',
            'isi' => 'Mohon update penimbangan kemarin.',
            'status' => 'baru',
        ]);

        $response = $this->patch(route('edukasi.pengaduan.tanggapi', $pengaduan->id), [
            'status' => 'selesai',
            'catatan' => 'Data penimbangan balita sudah diinputkan ke sistem KMS.',
        ]);

        $response->assertRedirect(route('edukasi.pengaduan.index'));
        $pengaduan->refresh();

        $this->assertEquals('selesai', $pengaduan->status);
        $this->assertEquals('Data penimbangan balita sudah diinputkan ke sistem KMS.', $pengaduan->catatan);
    }

    /**
     * Memastikan fungsi ekspor streaming rekap pengaduan menghasilkan file CSV.
     */
    public function test_export_generates_csv(): void
    {
        $response = $this->get(route('edukasi.pengaduan.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename=laporan_pengaduan_warga_', (string)$response->headers->get('Content-Disposition'));
    }
}
