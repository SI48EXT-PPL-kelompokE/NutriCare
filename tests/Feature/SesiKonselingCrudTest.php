<?php

namespace Tests\Feature;

use App\Models\OrangTua;
use App\Models\SesiKonseling;
use App\Models\User;
use App\Notifications\StatusKonselingNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SesiKonselingCrudTest extends TestCase
{
    use DatabaseTransactions;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = $this->userWithPermissions('Nakes / Bidan', [
            'konseling' => ['can_view', 'can_edit'],
        ]);
        $this->actingAs($this->staff);
    }

    private function resident(?OrangTua $profile = null): User
    {
        $user = $this->userWithPermissions('Warga / Orang Tua', ['konseling' => ['can_create']]);
        $profile?->update(['user_id' => $user->id]);

        return $user;
    }

    public function test_resident_cannot_submit_counseling_for_another_family(): void
    {
        $profile = OrangTua::factory()->create();
        $otherFamily = OrangTua::factory()->create();

        $this->actingAs($this->resident($profile))->get(route('konseling.create'))
            ->assertOk()
            ->assertSee($profile->name)
            ->assertDontSee($otherFamily->nik);

        $this->post(route('konseling.store'), [
            'orang_tua_id' => $otherFamily->id,
            'topik' => 'Konseling Gizi Balita',
        ])->assertForbidden();
    }

    /**
     * SCRUM-174 [KF-47]: Memastikan halaman indeks sesi konseling memuat kode 200 dan elemen antarmuka.
     */
    public function test_index_page_loads_successfully_and_displays_konseling_sessions(): void
    {
        $response = $this->get(route('konseling.index'));

        $response->assertStatus(200);
        $response->assertSee('Sesi Konseling Gizi Balita');
        $response->assertSee('Total Sesi');
        $response->assertSee('Menunggu Jadwal');
        $response->assertSee('Terjadwal Nakes');
        $response->assertSee('Selesai Konseling');
        $response->assertSee('searchKonselingInput');
    }

    /**
     * SCRUM-174 [KF-47]: Memastikan filter status dan pencarian kata kunci berfungsi dengan tepat.
     */
    public function test_filter_status_and_search_keyword_works(): void
    {
        $userNakes = User::first() ?? User::factory()->create();
        $orangTua = OrangTua::first() ?? OrangTua::create([
            'nik' => '3201019909990001',
            'name' => 'Ibu Siti Khadijah',
            'alamat' => 'Jl. Mawar No. 12',
            'kontak' => '081234567890',
            'pregnancy_status' => 'tidak_hamil',
        ]);

        SesiKonseling::create([
            'orang_tua_id' => $orangTua->id,
            'user_id' => $userNakes->id,
            'topik' => 'Pola Makan Balita Alergi Telur',
            'jadwal' => now()->addDays(2),
            'status' => 'dijadwalkan',
            'hasil' => null,
        ]);

        $responseSearch = $this->get(route('konseling.index', ['q' => 'Alergi Telur']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Pola Makan Balita Alergi Telur');

        $responseStatus = $this->get(route('konseling.index', ['status' => 'dijadwalkan']));
        $responseStatus->assertStatus(200);
        $responseStatus->assertSee('Pola Makan Balita Alergi Telur');
    }

    /**
     * SCRUM-174 [KF-47]: Memastikan form pengajuan konseling memuat data orang tua dan opsi topik.
     */
    public function test_create_page_loads_with_parent_data_and_topics(): void
    {
        $response = $this->actingAs($this->resident())->get(route('konseling.create'));

        $response->assertStatus(200);
        $response->assertSee('Formulir Pengajuan Konseling Gizi');
        $response->assertSee('Orang Tua dan Balita Terdaftar');
        $response->assertSee('Konseling Gizi Balita');
        $response->assertSee('Pencegahan Stunting');
    }

    /**
     * SCRUM-174 [KF-47]: Memastikan orang tua dapat mengajukan sesi konseling dengan status awal diajukan.
     */
    public function test_parent_can_submit_counseling_request_with_status_diajukan(): void
    {
        $orangTua = OrangTua::create([
            'nik' => '3201018808889999',
            'name' => 'Ibu Rahmawati Test Unique',
            'alamat' => 'Jl. Melati No. 5',
            'kontak' => '082199887766',
            'pregnancy_status' => 'tidak_hamil',
        ]);

        $postData = [
            'orang_tua_id' => $orangTua->id,
            'topik' => 'MPASI dan Pola Makan Sehat',
            'jadwal_harapan' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'keluhan' => 'Balita sulit mengonsumsi sayur dan berat badan stuck.',
        ];

        $response = $this->actingAs($this->resident($orangTua))->post(route('konseling.store'), $postData);

        $response->assertRedirect(route('konseling.create'));
        $response->assertSessionHas('success');

        $sesi = SesiKonseling::where('orang_tua_id', $orangTua->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($sesi);
        $this->assertEquals('diajukan', $sesi->status);
        $this->assertNull($sesi->user_id);
        $this->assertStringContainsString('Balita sulit mengonsumsi sayur', $sesi->hasil);
    }

    /**
     * SCRUM-174 [KF-47]: Memastikan nakes dapat menetapkan jadwal dan status berubah menjadi dijadwalkan.
     */
    public function test_nakes_can_schedule_counseling_session_and_status_becomes_dijadwalkan(): void
    {
        $orangTua = OrangTua::first() ?? OrangTua::create([
            'nik' => '3201017707770003',
            'name' => 'Ibu Fatimah Test',
            'alamat' => 'Jl. Kenanga No. 7',
            'kontak' => '085211223344',
            'pregnancy_status' => 'tidak_hamil',
        ]);

        $nakes = $this->staff;

        $sesi = SesiKonseling::create([
            'orang_tua_id' => $orangTua->id,
            'topik' => 'Konseling Gizi Balita',
            'status' => 'diajukan',
            'user_id' => null,
            'jadwal' => null,
            'hasil' => 'Keluhan nafsu makan berkurang',
        ]);

        $scheduleData = [
            'user_id' => $nakes->id,
            'jadwal' => now()->addDays(2)->setHour(10)->setMinute(0)->format('Y-m-d\TH:i'),
        ];

        $response = $this->patch(route('konseling.jadwalkan', $sesi->id), $scheduleData);

        $response->assertRedirect(route('konseling.show', $sesi->id));
        $response->assertSessionHas('success');

        $sesi->refresh();
        $this->assertEquals('dijadwalkan', $sesi->status);
        $this->assertEquals($nakes->id, $sesi->user_id);
        $this->assertNotNull($sesi->jadwal);
    }

    /**
     * SCRUM-174 [KF-47]: Memastikan hasil konseling tercatat dan status berubah menjadi selesai.
     */
    public function test_nakes_can_record_counseling_result_and_status_becomes_selesai(): void
    {
        $orangTua = OrangTua::first() ?? OrangTua::create([
            'nik' => '3201016606660004',
            'name' => 'Ibu Nurul Hidayah',
            'alamat' => 'Jl. Anggrek No. 10',
            'kontak' => '087788990011',
            'pregnancy_status' => 'tidak_hamil',
        ]);

        $nakes = $this->staff;

        $sesi = SesiKonseling::create([
            'orang_tua_id' => $orangTua->id,
            'user_id' => $nakes->id,
            'topik' => 'Pencegahan Stunting',
            'status' => 'dijadwalkan',
            'jadwal' => now()->subDay(),
            'hasil' => null,
        ]);

        $resultData = [
            'hasil' => 'Pemeriksaan antropometri menunjukkan z-score membaik. Disarankan konsumsi telur 1 butir per hari dan variasi puree hati ayam.',
        ];

        $response = $this->patch(route('konseling.selesai', $sesi->id), $resultData);

        $response->assertRedirect(route('konseling.show', $sesi->id));
        $response->assertSessionHas('success');

        $sesi->refresh();
        $this->assertEquals('selesai', $sesi->status);
        $this->assertStringContainsString('z-score membaik', $sesi->hasil);
    }

    /**
     * SCRUM-177 [KF-48]: Memastikan sistem menolak dan meminta pilih waktu lain jika nakes memiliki jadwal bentrok.
     */
    public function test_scheduling_fails_when_nakes_has_conflicting_schedule(): void
    {
        $orangTua = OrangTua::first() ?? OrangTua::create([
            'nik' => '3201015505550005',
            'name' => 'Ibu Dewi Sartika',
            'alamat' => 'Jl. Pahlawan No. 1',
            'kontak' => '081299887766',
            'pregnancy_status' => 'tidak_hamil',
        ]);

        $nakes = $this->staff;

        $jadwalAwal = now()->addDays(2)->setHour(9)->setMinute(0)->setSecond(0);

        // Sesi 1 yang sudah dijadwalkan
        SesiKonseling::create([
            'orang_tua_id' => $orangTua->id,
            'user_id' => $nakes->id,
            'topik' => 'Konseling Gizi Balita Sesi 1',
            'status' => 'dijadwalkan',
            'jadwal' => $jadwalAwal,
        ]);

        // Sesi 2 yang masih diajukan dan ingin dijadwalkan bentrok
        $sesi2 = SesiKonseling::create([
            'orang_tua_id' => $orangTua->id,
            'topik' => 'Konseling Gizi Balita Sesi 2',
            'status' => 'diajukan',
            'user_id' => null,
            'jadwal' => null,
        ]);

        // Jadwal bentrok (hanya selisih 30 menit dari sesi 1)
        $jadwalBentrok = $jadwalAwal->copy()->addMinutes(30)->format('Y-m-d\TH:i');

        $response = $this->patch(route('konseling.jadwalkan', $sesi2->id), [
            'user_id' => $nakes->id,
            'jadwal' => $jadwalBentrok,
        ]);

        $response->assertSessionHasErrors(['jadwal']);
        $this->assertStringContainsString('Nakes tidak tersedia: minta pilih waktu lain', session('errors')->first('jadwal'));

        $sesi2->refresh();
        $this->assertEquals('diajukan', $sesi2->status);
    }

    /**
     * SCRUM-177 [KF-48]: Memastikan endpoint cek ketersediaan nakes merespons JSON status ketersediaan dengan tepat.
     */
    public function test_cek_ketersediaan_api_returns_conflict_and_available_responses(): void
    {
        $orangTua = OrangTua::first() ?? OrangTua::create([
            'nik' => '3201014404440006',
            'name' => 'Ibu Kartini Test',
            'alamat' => 'Jl. Kartini No. 2',
            'kontak' => '081399887711',
            'pregnancy_status' => 'tidak_hamil',
        ]);

        $nakes = $this->staff;
        $jadwalPenuh = now()->addDays(3)->setHour(14)->setMinute(0)->setSecond(0);

        SesiKonseling::create([
            'orang_tua_id' => $orangTua->id,
            'user_id' => $nakes->id,
            'topik' => 'Stimulasi Tumbuh Kembang',
            'status' => 'dijadwalkan',
            'jadwal' => $jadwalPenuh,
        ]);

        // Cek pada jam yang sama (bentrok)
        $responseBentrok = $this->getJson(route('konseling.cek-ketersediaan', [
            'user_id' => $nakes->id,
            'jadwal' => $jadwalPenuh->format('Y-m-d\TH:i'),
        ]));

        $responseBentrok->assertStatus(200);
        $responseBentrok->assertJson([
            'available' => false,
        ]);

        // Cek pada waktu yang bebas (tidak bentrok)
        $jadwalBebas = $jadwalPenuh->copy()->addHours(3)->format('Y-m-d\TH:i');
        $responseBebas = $this->getJson(route('konseling.cek-ketersediaan', [
            'user_id' => $nakes->id,
            'jadwal' => $jadwalBebas,
        ]));

        $responseBebas->assertStatus(200);
        $responseBebas->assertJson([
            'available' => true,
        ]);
    }

    /**
     * SCRUM-180 & SCRUM-181 [KF-49]: Memastikan notifikasi perubahan status konseling terkirim pada setiap transisi status.
     */
    public function test_notification_sent_when_counseling_status_changes(): void
    {
        Notification::fake();

        $orangTua = OrangTua::create([
            'nik' => '3201019998887776',
            'name' => 'Ibu Notifikasi Test',
            'alamat' => 'Jl. Flamboyan No. 7',
            'kontak' => '081299881122',
            'pregnancy_status' => 'tidak_hamil',
        ]);

        $nakes = $this->staff;

        // 1. Uji transisi status 'diajukan' via store
        $responseStore = $this->actingAs($this->resident($orangTua))->post(route('konseling.store'), [
            'orang_tua_id' => $orangTua->id,
            'topik' => 'MPASI dan Pola Makan Sehat',
            'keluhan' => 'Anak kurang nafsu makan sayur dan buah',
        ]);
        $responseStore->assertRedirect(route('konseling.create'));

        Notification::assertSentTo(
            $orangTua,
            StatusKonselingNotification::class,
            function ($notification) {
                return $notification->status === 'diajukan';
            }
        );

        $sesi = SesiKonseling::where('orang_tua_id', $orangTua->id)->latest('id')->first();
        $this->assertNotNull($sesi);
        $this->assertEquals('diajukan', $sesi->status);

        // 2. Uji transisi status 'dijadwalkan' via jadwalkan
        $jadwal = now()->addDays(2)->setHour(10)->setMinute(0)->format('Y-m-d\TH:i');
        $responseJadwalkan = $this->actingAs($this->staff)->patch(route('konseling.jadwalkan', $sesi->id), [
            'user_id' => $nakes->id,
            'jadwal' => $jadwal,
        ]);
        $responseJadwalkan->assertRedirect(route('konseling.show', $sesi->id));

        Notification::assertSentTo(
            $orangTua,
            StatusKonselingNotification::class,
            function ($notification) {
                return $notification->status === 'dijadwalkan';
            }
        );

        $sesi->refresh();
        $this->assertEquals('dijadwalkan', $sesi->status);

        // 3. Uji transisi status 'selesai' via selesai
        $responseSelesai = $this->patch(route('konseling.selesai', $sesi->id), [
            'hasil' => 'Konseling gizi selesai. Diberikan panduan menu MPASI kaya protein hewani dan jadwal makan teratur.',
        ]);
        $responseSelesai->assertRedirect(route('konseling.show', $sesi->id));

        Notification::assertSentTo(
            $orangTua,
            StatusKonselingNotification::class,
            function ($notification) {
                return $notification->status === 'selesai';
            }
        );

        $sesi->refresh();
        $this->assertEquals('selesai', $sesi->status);
    }
}
