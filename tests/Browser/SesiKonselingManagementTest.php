<?php

namespace Tests\Browser;

use App\Models\OrangTua;
use App\Models\SesiKonseling;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SesiKonselingManagementTest extends DuskTestCase
{
    /**
     * SCRUM-176 [KF-47]: Menguji navigasi ke halaman indeks sesi konseling gizi dan filter pencarian.
     */
    public function test_user_can_view_konseling_index_and_use_search_and_filter(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/konseling')
                ->assertSee('Sesi Konseling Gizi Balita')
                ->assertPresent('#searchKonselingInput')
                ->assertPresent('#filterStatus')
                ->assertPresent('#filterTopik')
                ->type('#searchKonselingInput', 'MPASI')
                ->select('#filterStatus', 'diajukan')
                ->press('#btnFilterKonseling')
                ->assertQueryStringHas('q', 'MPASI')
                ->assertQueryStringHas('status', 'diajukan');
        });
    }

    /**
     * SCRUM-176 [KF-47]: Menguji halaman formulir pengajuan konseling gizi dan elemen dropdown.
     */
    public function test_parent_can_open_create_counseling_form_and_interact(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/konseling/create')
                ->assertSee('Formulir Pengajuan Konseling')
                ->assertPresent('#orang_tua_id')
                ->assertPresent('#topik')
                ->assertPresent('#jadwal_harapan')
                ->assertPresent('#keluhan')
                ->assertPresent('#btnSubmitKonseling');
        });
    }

    /**
     * SCRUM-179 [KF-48]: Menguji halaman detail sesi konseling dan antarmuka pengecekan bentrok nakes.
     */
    public function test_nakes_can_view_detail_and_check_availability_interface(): void
    {
        $orangTua = OrangTua::first() ?? OrangTua::create([
            'nik' => '3201017707770001',
            'name' => 'Ibu Dusk Test',
            'alamat' => 'Jl. Pengujian No. 5',
            'kontak' => '081234567800',
            'pregnancy_status' => 'tidak_hamil',
        ]);

        $sesi = SesiKonseling::create([
            'orang_tua_id' => $orangTua->id,
            'topik' => 'Stimulasi Tumbuh Kembang',
            'status' => 'diajukan',
            'user_id' => null,
            'jadwal' => null,
        ]);

        $this->browse(function (Browser $browser) use ($sesi) {
            $browser->visit('/konseling/' . $sesi->id)
                ->assertSee('Detail Sesi #' . $sesi->id)
                ->assertSee('Stimulasi Tumbuh Kembang')
                ->assertSee('Alur Perkembangan Sesi & Notifikasi')
                ->assertPresent('#user_id')
                ->assertPresent('#jadwal')
                ->assertPresent('#availabilityNotice')
                ->assertPresent('#btnJadwalkanKonseling');
        });
    }

    /**
     * SCRUM-182 [KF-49]: Menguji tampilan linimasa perkembangan status dan riwayat notifikasi pada UI peramban.
     */
    public function test_notification_and_status_progression_card_displayed(): void
    {
        $orangTua = OrangTua::first() ?? OrangTua::create([
            'nik' => '3201017707770002',
            'name' => 'Ibu Notifikasi Dusk',
            'alamat' => 'Jl. Pengujian No. 6',
            'kontak' => '081234567801',
            'pregnancy_status' => 'tidak_hamil',
        ]);

        $nakes = User::first() ?? User::factory()->create();

        $sesiSelesai = SesiKonseling::create([
            'orang_tua_id' => $orangTua->id,
            'user_id' => $nakes->id,
            'topik' => 'Konseling Gizi Balita Stunting',
            'status' => 'selesai',
            'jadwal' => now()->subDay(),
            'hasil' => 'Pola makan balita dievaluasi dan diberikan rekomendasi suplemen zat besi serta telur per hari.',
        ]);

        $this->browse(function (Browser $browser) use ($sesiSelesai) {
            $browser->visit('/konseling/' . $sesiSelesai->id)
                ->assertSee('Status: Selesai')
                ->assertSee('Riwayat & Status Notifikasi (KF-49)')
                ->assertSee('Catatan & Hasil Konseling Klinis')
                ->assertSee('Tercatat Permanen');
        });
    }
}

