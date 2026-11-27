<?php

namespace Tests\Feature;

use App\Models\Balita;
use App\Models\PengukuranKms;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SuratRujukan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratRujukanCrudTest extends TestCase
{
    use RefreshDatabase;

    protected Role $nakesRole;
    protected Role $wargaRole;
    protected Role $adminRole;
    protected User $nakes;
    protected User $warga;
    protected User $admin;
    protected Balita $balita;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nakesRole = Role::firstOrCreate(['name_role' => 'Nakes / Bidan'], ['description' => 'Nakes']);
        $this->wargaRole = Role::firstOrCreate(['name_role' => 'Warga / Orang Tua'], ['description' => 'Warga']);
        $this->adminRole = Role::firstOrCreate(['name_role' => 'Admin / Kepala Puskesmas'], ['description' => 'Admin']);

        // Seed permission rujukan untuk Nakes (view, create, edit, delete per proposal)
        Permission::firstOrCreate([
            'role_id' => $this->nakesRole->id,
            'module_name' => 'rujukan',
        ], [
            'name' => 'Rujukan',
            'can_view' => true,
            'can_create' => true,
            'can_edit' => true,
            'can_delete' => true,
        ]);

        $this->nakes = User::factory()->create([
            'role_id' => $this->nakesRole->id,
            'name' => 'Bidan Siti, S.Tr.Keb',
        ]);

        $this->warga = User::factory()->create([
            'role_id' => $this->wargaRole->id,
            'name' => 'Budi Warga',
        ]);

        $this->admin = User::factory()->create([
            'role_id' => $this->adminRole->id,
            'name' => 'Dr. Admin Kepala Puskesmas',
        ]);

        $this->balita = Balita::factory()->create(['name' => 'Kenzo Putra']);

        // Prasyarat KMS: sediakan hasil pengukuran KMS untuk $this->balita
        PengukuranKms::create([
            'balita_id' => $this->balita->id,
            'user_id' => $this->nakes->id,
            'date' => now()->toDateString(),
            'berat_badan' => 8.5,
            'tinggi_badan' => 75.0,
            'status_gizi' => 'Gizi Kurang',
        ]);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('rujukan.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_nakes_can_view_rujukan_index(): void
    {
        $surat = SuratRujukan::factory()->create([
            'balita_id' => $this->balita->id,
            'user_id' => $this->nakes->id,
        ]);

        $response = $this->actingAs($this->nakes)->get(route('rujukan.index'));
        $response->assertOk();
        $response->assertViewIs('operasional.rujukan.index');
        $response->assertViewHas('rujukanList');
        $response->assertViewHas('saranRujukan');
    }

    public function test_warga_cannot_view_rujukan_index(): void
    {
        $response = $this->actingAs($this->warga)->get(route('rujukan.index'));
        $response->assertForbidden();
    }

    public function test_nakes_can_view_create_page(): void
    {
        $response = $this->actingAs($this->nakes)->get(route('rujukan.create'));
        $response->assertOk();
        $response->assertViewIs('operasional.rujukan.create');
        $response->assertViewHas('balitaList');
    }

    public function test_nakes_can_store_surat_rujukan(): void
    {
        $payload = [
            'balita_id' => $this->balita->id,
            'faskes_tujuan' => 'RSUD dr. Soetomo Surabaya',
            'alasan' => 'Gizi buruk klinis dan stunting parah.',
            'status_follow_up' => 'belum',
        ];

        $response = $this->actingAs($this->nakes)->post(route('rujukan.store'), $payload);

        $surat = SuratRujukan::where('balita_id', $this->balita->id)->first();
        $this->assertNotNull($surat);
        $this->assertEquals($this->nakes->id, $surat->user_id);
        $this->assertNotEmpty($surat->nomor_surat);
        $this->assertNotEmpty($surat->qr_verification_code);

        $response->assertRedirect(route('rujukan.show', $surat));
    }

    public function test_store_validation_fails_for_missing_required_fields(): void
    {
        $response = $this->actingAs($this->nakes)->post(route('rujukan.store'), []);

        $response->assertSessionHasErrors(['balita_id', 'faskes_tujuan', 'alasan']);
    }

    public function test_warga_cannot_store_surat_rujukan(): void
    {
        $payload = [
            'balita_id' => $this->balita->id,
            'faskes_tujuan' => 'RSUD dr. Soetomo',
            'alasan' => 'Mencoba membuat surat',
            'status_follow_up' => 'belum',
        ];

        $response = $this->actingAs($this->warga)->post(route('rujukan.store'), $payload);
        $response->assertForbidden();
    }

    public function test_nakes_can_update_surat_rujukan(): void
    {
        $surat = SuratRujukan::factory()->create([
            'balita_id' => $this->balita->id,
            'user_id' => $this->nakes->id,
            'faskes_tujuan' => 'Puskesmas Lama',
            'alasan' => 'Alasan lama',
            'status_follow_up' => 'belum',
        ]);

        $originalNomor = $surat->nomor_surat;

        $response = $this->actingAs($this->nakes)->put(route('rujukan.update', $surat), [
            'faskes_tujuan' => 'RSUD Baru',
            'alasan' => 'Alasan baru setelah pemeriksaan lanjutan',
            'status_follow_up' => 'proses',
        ]);

        $response->assertRedirect(route('rujukan.show', $surat));

        $surat->refresh();
        $this->assertEquals('RSUD Baru', $surat->faskes_tujuan);
        $this->assertEquals('Alasan baru setelah pemeriksaan lanjutan', $surat->alasan);
        $this->assertEquals('proses', $surat->status_follow_up);
        // Nomor surat tidak boleh berubah
        $this->assertEquals($originalNomor, $surat->nomor_surat);
    }

    public function test_admin_can_delete_surat_rujukan(): void
    {
        $surat = SuratRujukan::factory()->create([
            'balita_id' => $this->balita->id,
            'user_id' => $this->nakes->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('rujukan.destroy', $surat));

        $response->assertRedirect(route('rujukan.index'));
        $this->assertDatabaseMissing('surat_rujukan', ['id' => $surat->id]);
    }

    public function test_nakes_can_delete_surat_rujukan(): void
    {
        $surat = SuratRujukan::factory()->create([
            'balita_id' => $this->balita->id,
            'user_id' => $this->nakes->id,
        ]);

        $response = $this->actingAs($this->nakes)->delete(route('rujukan.destroy', $surat));
        $response->assertRedirect(route('rujukan.index'));
        $this->assertDatabaseMissing('surat_rujukan', ['id' => $surat->id]);
    }

    public function test_warga_cannot_delete_surat_rujukan(): void
    {
        $surat = SuratRujukan::factory()->create([
            'balita_id' => $this->balita->id,
            'user_id' => $this->nakes->id,
        ]);

        $response = $this->actingAs($this->warga)->delete(route('rujukan.destroy', $surat));
        $response->assertForbidden();
    }

    public function test_nakes_can_access_pdf_download(): void
    {
        $surat = SuratRujukan::factory()->create([
            'balita_id' => $this->balita->id,
            'user_id' => $this->nakes->id,
        ]);

        $response = $this->actingAs($this->nakes)->get(route('rujukan.pdf', $surat));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_public_can_verify_valid_qr_code(): void
    {
        $surat = SuratRujukan::factory()->create([
            'balita_id' => $this->balita->id,
            'user_id' => $this->nakes->id,
        ]);

        $response = $this->get(route('rujukan.verify', ['code' => $surat->qr_verification_code]));
        $response->assertOk();
        $response->assertViewIs('operasional.rujukan.verify');
        $response->assertViewHas('surat');
        $response->assertSee($surat->nomor_surat);
    }

    public function test_store_fails_when_balita_has_no_kms_measurement(): void
    {
        $balitaTanpaKms = Balita::factory()->create(['name' => 'Balita Baru Tanpa KMS']);

        $payload = [
            'balita_id' => $balitaTanpaKms->id,
            'faskes_tujuan' => 'RSUD dr. Soetomo',
            'alasan' => 'Mencoba merujuk balita tanpa KMS',
            'status_follow_up' => 'belum',
        ];

        $response = $this->actingAs($this->nakes)->post(route('rujukan.store'), $payload);
        $response->assertSessionHasErrors(['balita_id']);
        $this->assertDatabaseMissing('surat_rujukan', ['balita_id' => $balitaTanpaKms->id]);
    }

    public function test_public_verify_invalid_code_shows_not_found(): void
    {
        $response = $this->get(route('rujukan.verify', ['code' => 'invalid-qr-random']));
        $response->assertOk();
        $response->assertViewIs('operasional.rujukan.verify');
        $response->assertViewHas('surat', null);
        $response->assertSee('TIDAK VALID');
    }

    public function test_public_verify_deleted_surat_shows_not_found(): void
    {
        $surat = SuratRujukan::factory()->create([
            'balita_id' => $this->balita->id,
            'user_id' => $this->nakes->id,
        ]);

        $code = $surat->qr_verification_code;

        // Surat dihapus
        $surat->delete();

        $response = $this->get(route('rujukan.verify', ['code' => $code]));
        $response->assertOk();
        $response->assertViewIs('operasional.rujukan.verify');
        $response->assertViewHas('surat', null);
        $response->assertSee('TIDAK VALID');
    }
}
