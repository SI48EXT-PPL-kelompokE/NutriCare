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
}

