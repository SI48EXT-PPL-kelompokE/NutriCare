<?php

namespace Tests\Feature;

use App\Enums\StatusGizi;
use App\Models\Balita;
use App\Models\PengukuranKms;
use App\Models\Role;
use App\Models\StandardLms;
use App\Models\User;
use App\Services\StatusGiziService;
use Database\Seeders\StandardLmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test integrasi penentuan status gizi (KF-15).
 *
 * Seluruh test memakai database nutricare_testing (lihat phpunit.xml) dan
 * tidak menyentuh database dev nutricare.
 */
class StatusGiziIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertEquals('nutricare_testing', config('database.connections.mysql.database'));
        $this->seed(StandardLmsSeeder::class);
    }

    /**
     * Buat user Nakes/Bidan beserta izin modul kms agar bisa mengakses route kms.
     */
    private function userNakes(): User
    {
        $role = Role::firstOrCreate(['name_role' => 'Nakes / Bidan']);
        $role->permissions()->updateOrCreate(
            ['module_name' => 'kms'],
            ['name' => 'kms', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true],
        );

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_store_status_dari_zscore_dan_input_status_diabaikan(): void
    {
        $user = $this->userNakes();
        $balita = Balita::factory()->create(['birth_date' => now()->subMonths(24)]);

        $response = $this->actingAs($user)->post(route('kms.store'), [
            'balita_id' => $balita->id,
            'date' => now()->subDay()->format('Y-m-d'),
            'berat_badan' => 8.5,
            'tinggi_badan' => 80,
            'status_gizi' => 'Gizi Buruk', // input palsu, harus diabaikan
        ]);

        $response->assertRedirect();
        $pengukuran = PengukuranKms::latest('id')->first();
        $this->assertNotNull($pengukuran->z_score_bbu);
        $this->assertNotNull($pengukuran->getRawOriginal('status_gizi'));
        $this->assertNotEquals('Gizi Buruk', $pengukuran->getRawOriginal('status_gizi'));
    }

    public function test_lengkap_dengan_fixture_lms_tbu_dan_bbtb(): void
    {
        // Fixture khusus test: tabel LMS TB_U dan BB_TB belum dimuat di produksi.
        StandardLms::create([
            'indikator' => 'TB_U', 'jenis_kelamin' => 'L', 'acuan_nilai' => 24,
            'l' => 0.1, 'm' => 80.0, 's' => 0.04,
            'sd_min3' => 70.9, 'sd_min2' => 73.9, 'sd_min1' => 76.9,
            'sd_0' => 80.0, 'sd_plus1' => 83.1, 'sd_plus2' => 86.2, 'sd_plus3' => 89.4,
        ]);
        StandardLms::create([
            'indikator' => 'BB_TB', 'jenis_kelamin' => 'L', 'acuan_nilai' => 80.0,
            'l' => 0.1, 'm' => 11.0, 's' => 0.1,
            'sd_min3' => 8.1, 'sd_min2' => 9.0, 'sd_min1' => 9.9,
            'sd_0' => 11.0, 'sd_plus1' => 12.2, 'sd_plus2' => 13.4, 'sd_plus3' => 14.8,
        ]);

        $user = $this->userNakes();
        $balita = Balita::factory()->create([
            'jenis_kelamin' => 'L',
            'birth_date' => now()->subMonths(24),
        ]);

        $this->actingAs($user)->post(route('kms.store'), [
            'balita_id' => $balita->id,
            'date' => now()->format('Y-m-d'),
            'berat_badan' => 11.0,
            'tinggi_badan' => 80.0,
        ])->assertRedirect();

        $pengukuran = PengukuranKms::latest('id')->first();
        $this->assertNotNull($pengukuran->z_score_bbu);
        $this->assertNotNull($pengukuran->z_score_tbu);
        $this->assertNotNull($pengukuran->z_score_bbtb);
        $this->assertTrue($pengukuran->hasil_status['lengkap']);
        $this->assertNotNull($pengukuran->getRawOriginal('status_gizi'));
    }

    public function test_partial_dan_all_null(): void
    {
        $partial = StatusGiziService::tentukan(-1.0, null, null);
        $this->assertFalse($partial['lengkap']);
        $this->assertSame(StatusGizi::NORMAL, $partial['status']);
        $this->assertNull(StatusGiziService::tentukan(null, null, null)['status']);
    }

    public function test_update_menghitung_ulang_status(): void
    {
        $user = $this->userNakes();
        $balita = Balita::factory()->create([
            'jenis_kelamin' => 'L',
            'birth_date' => now()->subMonths(24),
        ]);

        $pengukuran = PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'user_id' => $user->id,
            'date' => now()->subDays(2),
            'berat_badan' => 11.0,
            'tinggi_badan' => 85.0,
            'z_score_bbu' => null,
            'z_score_tbu' => null,
            'z_score_bbtb' => null,
            'status_gizi' => null,
        ]);

        $this->actingAs($user)->put(route('kms.update', $pengukuran), [
            'balita_id' => $balita->id,
            'date' => $pengukuran->date->format('Y-m-d'),
            'berat_badan' => 6.0, // turun drastis
            'tinggi_badan' => 85.0,
        ])->assertRedirect(route('kms.show', $pengukuran));

        $pengukuran->refresh();
        $this->assertNotNull($pengukuran->z_score_bbu);
        $this->assertSame(StatusGizi::BERAT_BADAN_SANGAT_KURANG, $pengukuran->status_gizi);
    }

    public function test_filter_status_dan_unknown_cast(): void
    {
        $user = $this->userNakes();
        PengukuranKms::factory()->create(['status_gizi' => 'Normal']);
        PengukuranKms::factory()->create(['status_gizi' => 'Status Lama Tidak Dikenal']);
        PengukuranKms::factory()->create(['status_gizi' => null]);

        $response = $this->actingAs($user)->get(route('kms.index', ['status' => 'Normal']));
        $response->assertOk();
        $response->assertSee('Normal');

        // Nilai lama yang tidak dikenal tidak membuat error, cast menjadi null
        $unknown = PengukuranKms::where('status_gizi', 'Status Lama Tidak Dikenal')->first();
        $this->assertNull($unknown->status_gizi);

        $this->actingAs($user)->get(route('kms.index', ['status' => '__null']))->assertOk();
    }

    public function test_filter_belum_ada_penilaian(): void
    {
        $user = $this->userNakes();
        $tanpaStatus = PengukuranKms::factory()->create(['status_gizi' => null]);
        $denganStatus = PengukuranKms::factory()->create(['status_gizi' => 'Normal']);

        $response = $this->actingAs($user)->get(route('kms.index', ['status' => '__null']));

        $response->assertOk();
        $response->assertViewHas('pengukurans', fn ($records) => $records->pluck('id')->contains($tanpaStatus->id)
            && ! $records->pluck('id')->contains($denganStatus->id));
    }

    public function test_command_status_dry_run_dan_apply(): void
    {
        $user = $this->userNakes();
        $balita = Balita::factory()->create([
            'jenis_kelamin' => 'L',
            'birth_date' => now()->subMonths(24),
        ]);

        $pengukuran = PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'user_id' => $user->id,
            'date' => now()->subDays(2),
            'z_score_bbu' => -2.5,
            'z_score_tbu' => null,
            'z_score_bbtb' => null,
            'status_gizi' => 'Gizi Buruk', // sengaja tidak sesuai z-score
        ]);

        // Dry-run: tidak mengubah data
        $this->artisan('kms:hitung-zscore --status')
            ->expectsOutputToContain('DRY-RUN')
            ->assertSuccessful();
        $this->assertSame('Gizi Buruk', $pengukuran->fresh()->getRawOriginal('status_gizi'));

        // Apply: status dihitung ulang dari z-score (BB/U -2.5 -> Berat Badan Kurang)
        $this->artisan('kms:hitung-zscore --status --apply')
            ->assertSuccessful();
        $this->assertSame('Berat Badan Kurang', $pengukuran->fresh()->getRawOriginal('status_gizi'));
    }

    public function test_show_menampilkan_kategori_dan_catatan(): void
    {
        $user = $this->userNakes();
        $pengukuran = PengukuranKms::factory()->create([
            'z_score_bbu' => -2.5,
            'z_score_tbu' => null,
            'z_score_bbtb' => null,
            'status_gizi' => 'Berat Badan Kurang',
        ]);

        $this->actingAs($user)
            ->get(route('kms.show', $pengukuran))
            ->assertOk()
            ->assertSee('BB/U')
            ->assertSee('Penilaian sebagian')
            ->assertSee('Status gizi ini adalah alat bantu pemantauan');
    }
}
