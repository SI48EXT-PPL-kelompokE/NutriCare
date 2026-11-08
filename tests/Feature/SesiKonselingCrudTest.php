<?php

namespace Tests\Feature;

use App\Models\OrangTua;
use App\Models\SesiKonseling;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SesiKonselingCrudTest extends TestCase
{
    use DatabaseTransactions;

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
        $response = $this->get(route('konseling.create'));

        $response->assertStatus(200);
        $response->assertSee('Formulir Pengajuan Konseling Gizi');
        $response->assertSee('Orang Tua &amp; Balita Terdaftar', false);
        $response->assertSee('Konseling Gizi Balita');
        $response->assertSee('Pencegahan Stunting');
    }

    /**
     * SCRUM-174 [KF-47]: Memastikan orang tua dapat mengajukan sesi konseling dengan status awal diajukan.
     */
    public function test_parent_can_submit_counseling_request_with_status_diajukan(): void
    {
        $orangTua = OrangTua::first() ?? OrangTua::create([
            'nik' => '3201018808880002',
            'name' => 'Ibu Rahmawati Test',
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

        $response = $this->post(route('konseling.store'), $postData);

        $response->assertRedirect(route('konseling.index'));
        $response->assertSessionHas('success');

        $sesi = SesiKonseling::where('orang_tua_id', $orangTua->id)
            ->where('topik', 'MPASI dan Pola Makan Sehat')
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

        $nakes = User::first() ?? User::factory()->create();

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

        $nakes = User::first() ?? User::factory()->create();

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
}
