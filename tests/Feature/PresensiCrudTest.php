<?php

namespace Tests\Feature;

use App\Models\Balita;
use App\Models\JadwalPosyandu;
use App\Models\OrangTua;
use App\Models\Permission;
use App\Models\Presensi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresensiCrudTest extends TestCase
{
    use RefreshDatabase;

    protected Role $kaderRole;
    protected Role $wargaRole;
    protected Role $adminRole;
    protected User $kader;
    protected User $warga;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kaderRole = Role::firstOrCreate(['name_role' => 'Kader Posyandu'], ['description' => 'Kader']);
        $this->wargaRole = Role::firstOrCreate(['name_role' => 'Warga / Orang Tua'], ['description' => 'Warga']);
        $this->adminRole = Role::firstOrCreate(['name_role' => 'Admin / Kepala Puskesmas'], ['description' => 'Admin']);

        // Seed permission presensi untuk Kader
        Permission::firstOrCreate([
            'role_id' => $this->kaderRole->id,
            'module_name' => 'presensi',
        ], [
            'name' => 'Presensi',
            'can_view' => true,
            'can_create' => true,
            'can_edit' => true,
            'can_delete' => true,
        ]);

        $this->kader = User::factory()->create(['role_id' => $this->kaderRole->id]);
        $this->warga = User::factory()->create(['role_id' => $this->wargaRole->id]);
        $this->admin = User::factory()->create(['role_id' => $this->adminRole->id]);
    }

    public function test_warga_cannot_access_presensi_or_check_in(): void
    {
        $jadwal = JadwalPosyandu::factory()->create(['status' => 'aktif']);

        // Warga dilarang melihat halaman presensi
        $this->actingAs($this->warga)
            ->get(route('presensi.index'))
            ->assertStatus(403);

        // Warga dilarang melakukan check-in QR
        $this->actingAs($this->warga)
            ->postJson(route('presensi.check-in'), [
                'jadwal_id' => $jadwal->id,
                'qr_code' => 'NC-ANYTHING',
            ])
            ->assertStatus(403);
    }

    public function test_kader_can_access_presensi_index(): void
    {
        $jadwal = JadwalPosyandu::factory()->create(['status' => 'aktif']);

        $response = $this->actingAs($this->kader)
            ->get(route('presensi.index', ['jadwal_id' => $jadwal->id]));

        $response->assertStatus(200);
        $response->assertViewIs('operasional.presensi.index');
    }

    public function test_kader_can_check_in_via_qr_json(): void
    {
        $jadwal = JadwalPosyandu::factory()->create(['status' => 'aktif']);
        $balita = Balita::factory()->create(['qr_code' => 'NC-QR001']);

        $response = $this->actingAs($this->kader)
            ->postJson(route('presensi.check-in'), [
                'jadwal_id' => $jadwal->id,
                'qr_code' => 'NC-QR001',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('is_new', true)
            ->assertJsonPath('balita.name', $balita->name);

        $this->assertDatabaseHas('presensi', [
            'jadwal_id' => $jadwal->id,
            'balita_id' => $balita->id,
            'presence_status' => 'hadir',
        ]);
    }

    public function test_check_in_with_invalid_qr_returns_422(): void
    {
        $jadwal = JadwalPosyandu::factory()->create(['status' => 'aktif']);

        $response = $this->actingAs($this->kader)
            ->postJson(route('presensi.check-in'), [
                'jadwal_id' => $jadwal->id,
                'qr_code' => 'NC-INVALID-CODE',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['qr_code']);
    }

    public function test_repeat_scan_returns_idempotent_status(): void
    {
        $jadwal = JadwalPosyandu::factory()->create(['status' => 'aktif']);
        $balita = Balita::factory()->create(['qr_code' => 'NC-QR002']);

        // Scan pertama
        $this->actingAs($this->kader)
            ->postJson(route('presensi.check-in'), [
                'jadwal_id' => $jadwal->id,
                'qr_code' => 'NC-QR002',
            ])->assertStatus(201);

        // Scan kedua (berulang)
        $response = $this->actingAs($this->kader)
            ->postJson(route('presensi.check-in'), [
                'jadwal_id' => $jadwal->id,
                'qr_code' => 'NC-QR002',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'already_checked_in')
            ->assertJsonPath('is_new', false);

        $this->assertSame(1, Presensi::where('jadwal_id', $jadwal->id)->where('balita_id', $balita->id)->count());
    }

    public function test_kader_can_update_presence_status(): void
    {
        $presensi = Presensi::factory()->create(['presence_status' => 'hadir']);

        $response = $this->actingAs($this->kader)
            ->patchJson(route('presensi.update', $presensi), [
                'presence_status' => 'tidak_hadir',
            ]);

        $response->assertStatus(200);
        $this->assertSame('tidak_hadir', $presensi->fresh()->presence_status);
    }

    public function test_kader_can_delete_presensi(): void
    {
        $presensi = Presensi::factory()->create();

        $response = $this->actingAs($this->kader)
            ->deleteJson(route('presensi.destroy', $presensi));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('presensi', ['id' => $presensi->id]);
    }
}
