<?php

namespace Tests\Feature;

use App\Models\OrangTua;
use App\Models\Pengaduan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengaduanCrudTest extends TestCase
{
    use DatabaseTransactions;

    private function adminUser(): User
    {
        return $this->userWithPermissions(Role::ADMIN, [
            'artikel' => ['can_view'],
            'laporan' => ['can_create'],
            'pengaduan' => ['can_view', 'can_edit'],
        ]);
    }

    private function residentUser(): User
    {
        return $this->userWithPermissions(Role::RESIDENT, [
            'artikel' => ['can_view'],
            'pengaduan' => ['can_create'],
        ]);
    }

    /**
     * Memastikan admin dapat melihat daftar & ekspor pengaduan, namun tidak dapat mengakses pembuatan aduan warga.
     */
    public function test_index_page_loads_for_admin_without_create_access(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(route('edukasi.pengaduan.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengaduan Warga');
        $response->assertSee('Ekspor Rekap');
        $response->assertSee('KATEGORI PENGADUAN');
        $response->assertSee('RINGKASAN PENGADUAN');
        $response->assertDontSee('Catat Pengaduan');
        $response->assertDontSee('Buat Pengaduan');

        $this->actingAs($admin)->get(route('edukasi.pengaduan.create'))->assertForbidden();
    }

    /**
     * Memastikan warga melihat tab Pengaduan Warga, tombol Buat Pengaduan, dan diblokir dari aksi admin.
     */
    public function test_resident_can_access_pengaduan_and_is_blocked_from_admin_actions(): void
    {
        $resident = $this->residentUser();
        $pengaduan = Pengaduan::create([
            'kategori' => 'Layanan Posyandu',
            'judul' => 'Uji Akses Warga',
            'isi' => 'Laporan anonim warga.',
            'is_anonymous' => true,
            'status' => 'baru',
        ]);

        $this->actingAs($resident)
            ->get(route('edukasi.artikel.index'))
            ->assertOk()
            ->assertSee('Pengaduan Warga');

        $this->actingAs($resident)
            ->get(route('edukasi.pengaduan.index'))
            ->assertOk()
            ->assertSee('Buat Pengaduan')
            ->assertDontSee('Ekspor Rekap')
            ->assertDontSee('Tanggapi');

        $this->actingAs($resident)
            ->get(route('edukasi.pengaduan.show', $pengaduan->id))
            ->assertOk()
            ->assertDontSee('Simpan Tanggapan');

        $this->actingAs($resident)->get(route('edukasi.pengaduan.export'))->assertForbidden();
        $this->actingAs($resident)->patch(route('edukasi.pengaduan.tanggapi', $pengaduan->id), [
            'status' => 'selesai',
            'catatan' => 'Tidak sah.',
        ])->assertForbidden();
    }

    /**
     * SCRUM-189 [KF-52]: Memastikan warga dapat mengirim pengaduan dengan opsi anonim.
     */
    public function test_resident_store_creates_anonymous_complaint(): void
    {
        $resident = $this->residentUser();

        $payload = [
            'kategori' => 'Distribusi PMT',
            'judul' => 'Keluhan Pembagian PMT Posyandu',
            'isi' => 'Pembagian PMT belum merata di meja 3.',
            'is_anonymous' => 1,
        ];

        $response = $this->actingAs($resident)->post(route('edukasi.pengaduan.store'), $payload);

        $response->assertRedirect(route('edukasi.pengaduan.index'));
        $this->assertDatabaseHas('pengaduan', [
            'judul' => 'Keluhan Pembagian PMT Posyandu',
            'is_anonymous' => true,
            'orang_tua_id' => null,
            'status' => 'baru',
        ]);
    }

    /**
     * SCRUM-186 [KF-51]: Memastikan warga dapat melampirkan foto bukti fisik dan tertaut ke profil orang tuanya.
     */
    public function test_resident_store_uploads_proof_photo_for_own_profile(): void
    {
        Storage::fake('public');

        $resident = $this->residentUser();
        $orangTua = OrangTua::create([
            'user_id' => $resident->id,
            'nik' => '3201010101990001',
            'name' => $resident->name,
            'alamat' => 'Posyandu Melati',
            'kontak' => '081234567890',
            'pregnancy_status' => 'tidak_hamil',
        ]);
        $file = UploadedFile::fake()->image('bukti_rusak.jpg', 600, 400)->size(500);

        $payload = [
            'kategori' => 'Layanan Posyandu',
            'judul' => 'Timbangan Bayi Rusak',
            'isi' => 'Timbangan di posyandu perlu dikalibrasi ulang.',
            'is_anonymous' => 0,
            'foto_bukti' => $file,
        ];

        $response = $this->actingAs($resident)->post(route('edukasi.pengaduan.store'), $payload);

        $response->assertRedirect(route('edukasi.pengaduan.index'));
        $pengaduan = Pengaduan::where('judul', 'Timbangan Bayi Rusak')->first();

        $this->assertNotNull($pengaduan);
        $this->assertSame($orangTua->id, $pengaduan->orang_tua_id);
        $this->assertNotNull($pengaduan->foto_bukti_path);
        Storage::disk('public')->assertExists($pengaduan->foto_bukti_path);
    }

    /**
     * SCRUM-183 [KF-50]: Memastikan admin dapat menanggapi pengaduan dan mengubah status.
     */
    public function test_admin_can_respond_and_update_status(): void
    {
        $admin = $this->adminUser();
        $pengaduan = Pengaduan::create([
            'kategori' => 'Data KMS',
            'judul' => 'Data Berat Badan Belum Masuk',
            'isi' => 'Mohon update penimbangan kemarin.',
            'status' => 'baru',
        ]);

        $response = $this->actingAs($admin)->patch(route('edukasi.pengaduan.tanggapi', $pengaduan->id), [
            'status' => 'selesai',
            'catatan' => 'Data penimbangan balita sudah diinputkan ke sistem KMS.',
        ]);

        $response->assertRedirect(route('edukasi.pengaduan.index'));
        $pengaduan->refresh();

        $this->assertEquals('selesai', $pengaduan->status);
        $this->assertEquals('Data penimbangan balita sudah diinputkan ke sistem KMS.', $pengaduan->catatan);
    }

    /**
     * Memastikan fungsi ekspor streaming rekap pengaduan menghasilkan file CSV bagi admin.
     */
    public function test_export_generates_csv(): void
    {
        $admin = $this->adminUser();
        $response = $this->actingAs($admin)->get(route('edukasi.pengaduan.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename=laporan_pengaduan_warga_', (string) $response->headers->get('Content-Disposition'));
    }
}
