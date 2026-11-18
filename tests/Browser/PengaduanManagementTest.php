<?php

namespace Tests\Browser;

use App\Models\Pengaduan;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PengaduanManagementTest extends DuskTestCase
{
    /**
     * SCRUM-185 [KF-50]: Menguji navigasi halaman daftar pengaduan warga serta filter pencarian.
     */
    public function test_user_can_view_pengaduan_index_and_use_search_filter(): void
    {
        $admin = $this->activeUserForRole('Admin / Kepala Puskesmas');

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)->visit('/edukasi/pengaduan')
                ->assertSee('Edukasi & Layanan Warga')
                ->assertSee('Pengaduan Warga')
                ->assertPresent('#btnCatatPengaduan')
                ->assertPresent('#searchPengaduanInput')
                ->assertPresent('#filterKategoriPengaduan')
                ->assertPresent('#filterStatusPengaduan')
                ->assertPresent('#btnEksporRekap')
                ->type('#searchPengaduanInput', 'PMT')
                ->waitUntil('window.location.search.includes("q=PMT")', 5)
                ->assertQueryStringHas('q', 'PMT');
        });
    }

    /**
     * SCRUM-188 [KF-51] & SCRUM-191 [KF-52]: Menguji form catat pengaduan anonim beserta upload foto bukti di browser.
     */
    public function test_user_can_submit_anonymous_complaint_with_photo_via_browser(): void
    {
        $admin = $this->activeUserForRole('Admin / Kepala Puskesmas');
        $tmpPng = tempnam(sys_get_temp_dir(), 'dusk_bukti_') . '.png';
        // Minimal valid 1x1 PNG binary
        file_put_contents($tmpPng, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII='
        ));

        $judul = 'Laporan Anonim Dusk ' . uniqid();
        $isi = 'Simulasi pengiriman pengaduan anonim dengan foto bukti melalui Laravel Dusk ' . uniqid() . '.';

        try {
            $this->browse(function (Browser $browser) use ($admin, $tmpPng, $judul, $isi) {
                $browser->loginAs($admin)->visit('/edukasi/pengaduan/create')
                    ->assertSee('Catat Pengaduan Warga Baru')
                    ->select('#kategori', 'Distribusi PMT')
                    ->check('input[name="is_anonymous"]')
                    ->type('#judul', $judul)
                    ->type('#isi', $isi)
                    ->attach('#foto_bukti', $tmpPng)
                    ->press('Kirim Pengaduan')
                    ->assertPathIs('/edukasi/pengaduan')
                    ->assertSee($isi)
                    ->assertSee('Anonim');
            });
        } finally {
            if (file_exists($tmpPng)) {
                @unlink($tmpPng);
            }
            Pengaduan::where('judul', $judul)->delete();
        }
    }

    /**
     * SCRUM-185 [KF-50]: Menguji halaman detail pengaduan dan penyimpanan tanggapan petugas.
     */
    public function test_admin_can_open_detail_and_submit_response(): void
    {
        $admin = $this->activeUserForRole('Admin / Kepala Puskesmas');

        $pengaduan = Pengaduan::create([
            'kategori' => 'Layanan Posyandu',
            'judul' => 'Uji Tanggapan Dusk ' . uniqid(),
            'isi' => 'Menunggu tindak lanjut dari petugas Puskesmas.',
            'status' => 'baru',
        ]);

        try {
            $this->browse(function (Browser $browser) use ($admin, $pengaduan) {
                $browser->loginAs($admin)->visit('/edukasi/pengaduan/' . $pengaduan->id)
                    ->assertSee($pengaduan->judul)
                    ->assertSee('Tindak Lanjut & Tanggapan Petugas')
                    ->select('#status', 'selesai')
                    ->type('#catatan', 'Keluhan sudah ditindaklanjuti dan diselesaikan oleh petugas.')
                    ->press('Simpan Tanggapan')
                    ->assertPathIs('/edukasi/pengaduan')
                    ->assertSee($pengaduan->nomor_tiket);
            });
        } finally {
            $pengaduan->delete();
        }
    }
}
