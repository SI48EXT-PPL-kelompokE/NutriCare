<?php

namespace Tests\Feature;

use App\Models\Balita;
use App\Models\OrangTua;
use App\Models\PengukuranKms;
use App\Models\Role;
use App\Models\StandardLms;
use App\Models\User;
use App\Services\GrowthChartService;
use App\Services\StatusGiziService;
use Database\Seeders\StandardLmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature test halaman grafik pertumbuhan balita (KF-16).
 *
 * Memakai database nutricare_testing (lihat phpunit.xml); tidak menyentuh
 * database dev nutricare.
 */
class GrafikPertumbuhanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertEquals('nutricare_testing', config('database.connections.mysql.database'));
        $this->seed(StandardLmsSeeder::class);
    }

    /**
     * User nakes dengan izin modul kms dan balita (termasuk view-balita-identity).
     */
    private function userNakes(): User
    {
        $role = Role::firstOrCreate(['name_role' => 'Nakes / Bidan']);
        $role->permissions()->updateOrCreate(
            ['module_name' => 'kms'],
            ['name' => 'kms', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true],
        );
        $role->permissions()->updateOrCreate(
            ['module_name' => 'balita'],
            ['name' => 'balita', 'can_view' => true],
        );

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function userKader(): User
    {
        $role = Role::firstOrCreate(['name_role' => 'Kader Posyandu']);
        $role->permissions()->updateOrCreate(
            ['module_name' => 'kms'],
            ['name' => 'kms', 'can_view' => true, 'can_create' => true],
        );

        return User::factory()->create(['role_id' => $role->id]);
    }

    /**
     * User warga beserta entitas OrangTua miliknya.
     */
    private function userWarga(): array
    {
        $role = Role::firstOrCreate(['name_role' => Role::RESIDENT]);
        $user = User::factory()->create(['role_id' => $role->id]);
        $orangTua = OrangTua::factory()->create(['user_id' => $user->id]);

        return [$user, $orangTua];
    }

    private function balita(OrangTua $orangTua, string $nama = 'Anak Uji', string $jk = 'L'): Balita
    {
        return Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'name' => $nama,
            'jenis_kelamin' => $jk,
            'birth_date' => '2025-06-01',
        ]);
    }

    private function ukur(Balita $balita, User $petugas, string $tanggal, float $berat, float $tinggi, ?float $zBbu = null): PengukuranKms
    {
        return PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'user_id' => $petugas->id,
            'date' => $tanggal,
            'berat_badan' => $berat,
            'tinggi_badan' => $tinggi,
            'z_score_bbu' => $zBbu,
            'z_score_tbu' => null,
            'z_score_bbtb' => null,
            'status_gizi' => $zBbu !== null ? StatusGiziService::tentukan($zBbu, null, null)['status']?->value : null,
        ]);
    }

    public function test_guest_diarahkan_ke_login(): void
    {
        $orangTua = OrangTua::factory()->create();
        $balita = $this->balita($orangTua);

        $this->get(route('balita.pertumbuhan', $balita))
            ->assertRedirect(route('login'));
    }

    public function test_nakes_dan_kader_dapat_membuka_halaman(): void
    {
        $orangTua = OrangTua::factory()->create();

        $nakes = $this->userNakes();
        $balita = $this->balita($orangTua);
        $this->ukur($balita, $nakes, '2026-01-01', 8.5, 74.0, -0.5);
        $this->actingAs($nakes)->get(route('balita.pertumbuhan', $balita))->assertOk();

        $kader = $this->userKader();
        $this->actingAs($kader)->get(route('balita.pertumbuhan', $balita))->assertOk();
    }

    public function test_warga_dapat_membuka_anaknya_dan_403_untuk_anak_orang_lain(): void
    {
        [$warga, $orangTua] = $this->userWarga();
        $anak = $this->balita($orangTua, 'Anak Sendiri');

        $orangTuaLain = OrangTua::factory()->create();
        $anakOrangLain = $this->balita($orangTuaLain, 'Anak Orang Lain');

        $this->actingAs($warga)->get(route('balita.pertumbuhan', $anak))->assertOk();
        $this->actingAs($warga)->get(route('balita.pertumbuhan', $anakOrangLain))->assertForbidden();
    }

    public function test_nik_tidak_tampil_untuk_warga_tampil_untuk_nakes(): void
    {
        $nik = '3271234567890001';
        $role = Role::firstOrCreate(['name_role' => Role::RESIDENT]);
        /** @var User $warga */
        $warga = User::factory()->create(['role_id' => $role->id]);
        $orangTua = OrangTua::factory()->create(['user_id' => $warga->id, 'nik' => $nik]);
        $balita = $this->balita($orangTua, 'Anak Warga');

        $this->actingAs($warga)->get(route('balita.pertumbuhan', $balita))
            ->assertOk()
            ->assertDontSee($nik);

        $nakes = $this->userNakes();
        $this->actingAs($nakes)->get(route('balita.pertumbuhan', $balita))
            ->assertOk()
            ->assertSee($nik);
    }

    public function test_balita_tanpa_pengukuran_menampilkan_pesan_kosong(): void
    {
        $nakes = $this->userNakes();
        $orangTua = OrangTua::factory()->create();
        $balita = $this->balita($orangTua, 'Anak Kosong');

        $this->actingAs($nakes)->get(route('balita.pertumbuhan', $balita))
            ->assertOk()
            ->assertSee('Belum ada data pengukuran.');
    }

    public function test_atribut_data_chart_berisi_json_valid(): void
    {
        $nakes = $this->userNakes();
        $orangTua = OrangTua::factory()->create();
        $balita = $this->balita($orangTua, 'Anak Chart');
        $this->ukur($balita, $nakes, '2026-01-10', 8.9, 74.0, -0.5);
        $this->ukur($balita, $nakes, '2026-03-10', 9.3, 76.0, -0.4);

        $response = $this->actingAs($nakes)->get(route('balita.pertumbuhan', $balita));
        $response->assertOk();

        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/data-chart="([^"]+)"/', $html, 'Atribut data-chart tidak ditemukan.');

        preg_match('/data-chart="([^"]+)"/', $html, $m);
        $payload = json_decode(html_entity_decode($m[1], ENT_QUOTES), true);

        $this->assertIsArray($payload);
        $this->assertArrayHasKey('balita', $payload);
        $this->assertArrayHasKey('indikator', $payload);
        $this->assertArrayHasKey('bb_u', $payload['indikator']);
        $this->assertArrayHasKey('tb_u', $payload['indikator']);
        $this->assertArrayHasKey('bb_tb', $payload['indikator']);
        $this->assertCount(2, $payload['indikator']['bb_u']['titik']);
        $this->assertSame(2, $payload['jumlah_pengukuran']);
    }

    public function test_catatan_kurva_dan_kurva_muncul_bila_lms_sisipkan(): void
    {
        $nakes = $this->userNakes();
        $orangTua = OrangTua::factory()->create();
        $balita = $this->balita($orangTua, 'Anak Kurva');
        $this->ukur($balita, $nakes, '2026-01-01', 12.0, 88.0, -0.5);

        // TB_U belum ada -> catatan muncul.
        $this->actingAs($nakes)->get(route('balita.pertumbuhan', $balita))
            ->assertOk()
            ->assertSee('Kurva acuan WHO untuk indikator ini belum tersedia');

        // Sisipkan baris LMS TB_U buatan.
        StandardLms::create([
            'indikator' => 'TB_U', 'jenis_kelamin' => 'L', 'acuan_nilai' => 12,
            'l' => 0.1, 'm' => 75.0, 's' => 0.04,
            'sd_min3' => 66.0, 'sd_min2' => 69.0, 'sd_min1' => 72.0,
            'sd_0' => 75.0, 'sd_plus1' => 78.0, 'sd_plus2' => 81.0, 'sd_plus3' => 84.0,
        ]);

        $grafik = (new GrowthChartService())->bangun($balita->fresh());
        $this->assertNotNull($grafik['indikator']['tb_u']['kurva']);
        $this->assertNull($grafik['indikator']['tb_u']['catatan_kurva']);
    }

    public function test_halaman_kms_anak_saya(): void
    {
        // Tanpa anak -> empty state.
        [$wargaKosong] = $this->userWarga();
        $this->actingAs($wargaKosong)->get(route('kms.anak-saya'))
            ->assertOk()
            ->assertSee('Belum ada data anak terdaftar.');

        // Satu anak -> redirect ke halaman grafik.
        [$wargaSatu, $orangTuaSatu] = $this->userWarga();
        $anakSatu = $this->balita($orangTuaSatu, 'Anak Tunggal');
        $this->actingAs($wargaSatu)->get(route('kms.anak-saya'))
            ->assertRedirect(route('balita.pertumbuhan', $anakSatu));

        // Banyak anak -> daftar kartu.
        [$wargaBanyak, $orangTuaBanyak] = $this->userWarga();
        $this->balita($orangTuaBanyak, 'Anak Pertama');
        $this->balita($orangTuaBanyak, 'Anak Kedua');
        $this->actingAs($wargaBanyak)->get(route('kms.anak-saya'))
            ->assertOk()
            ->assertSee('Anak Pertama')
            ->assertSee('Anak Kedua')
            ->assertSee('Lihat Grafik');
    }

    public function test_status_ringkasan_sama_dengan_status_gizi(): void
    {
        $nakes = $this->userNakes();
        $orangTua = OrangTua::factory()->create();
        $balita = $this->balita($orangTua, 'Anak Status');
        // BB/U -2.5 -> "Berat Badan Kurang"
        $pengukuran = $this->ukur($balita, $nakes, '2026-01-01', 8.0, 74.0, -2.5);

        $hasil = StatusGiziService::untukPengukuran($pengukuran);
        $grafik = (new GrowthChartService())->bangun($balita);

        $this->assertSame($hasil['status'], $grafik['ringkasan']['status']['nilai']);

        $this->actingAs($nakes)->get(route('balita.pertumbuhan', $balita))
            ->assertOk()
            ->assertSee('Berat Badan Kurang');
    }

    public function test_kinerja_jumlah_query_dan_waktu_respons(): void
    {
        $nakes = $this->userNakes();
        $orangTua = OrangTua::factory()->create();
        $balita = $this->balita($orangTua, 'Anak Kinerja');
        $this->ukur($balita, $nakes, '2026-01-10', 8.9, 74.0, -0.5);
        $this->ukur($balita, $nakes, '2026-03-10', 9.3, 76.0, -0.4);

        $this->actingAs($nakes);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $mulai = microtime(true);
        $response = $this->get(route('balita.pertumbuhan', $balita));
        $waktuMs = (int) round((microtime(true) - $mulai) * 1000);

        $jumlahQuery = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();

        fwrite(STDERR, "\n[KNF-01] jumlah query={$jumlahQuery}, waktu respons={$waktuMs} ms\n");

        $this->assertLessThanOrEqual(10, $jumlahQuery, "Jumlah query {$jumlahQuery} melebihi 10.");
        $this->assertLessThan(2000, $waktuMs, "Waktu respons {$waktuMs} ms melebihi 2000 ms.");
    }
}
