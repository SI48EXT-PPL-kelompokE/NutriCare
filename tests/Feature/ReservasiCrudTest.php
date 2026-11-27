<?php

namespace Tests\Feature;

use App\Models\Balita;
use App\Models\JadwalPosyandu;
use App\Models\OrangTua;
use App\Models\Permission;
use App\Models\Reservasi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservasiCrudTest extends TestCase
{
    use RefreshDatabase;

    protected Role $wargaRole;
    protected Role $kaderRole;
    protected Role $adminRole;
    protected User $wargaUser;
    protected OrangTua $orangTua;
    protected Balita $balita;
    protected User $kaderUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wargaRole = Role::firstOrCreate(['name_role' => 'Warga / Orang Tua'], ['description' => 'Warga']);
        $this->kaderRole = Role::firstOrCreate(['name_role' => 'Kader Posyandu'], ['description' => 'Kader']);
        $this->adminRole = Role::firstOrCreate(['name_role' => 'Admin / Kepala Puskesmas'], ['description' => 'Admin']);

        // Seed permission reservasi untuk Warga
        Permission::firstOrCreate([
            'role_id' => $this->wargaRole->id,
            'module_name' => 'reservasi',
        ], [
            'name' => 'Reservasi',
            'can_view' => true,
            'can_create' => true,
            'can_edit' => true,
            'can_delete' => true,
        ]);

        $this->wargaUser = User::factory()->create(['role_id' => $this->wargaRole->id]);
        $this->orangTua = OrangTua::factory()->create(['user_id' => $this->wargaUser->id]);
        $this->balita = Balita::factory()->create(['orang_tua_id' => $this->orangTua->id]);

        $this->kaderUser = User::factory()->create(['role_id' => $this->kaderRole->id]);
    }

    public function test_unauthenticated_cannot_access_reservasi(): void
    {
        $this->get(route('reservasi.index'))->assertRedirect(route('login'));
        $this->post(route('reservasi.store'), [])->assertRedirect(route('login'));
    }

    public function test_warga_sees_only_own_reservations(): void
    {
        $jadwal = JadwalPosyandu::factory()->create(['status' => 'aktif']);

        // Reservasi milik warga ini
        $myReservasi = Reservasi::factory()->create([
            'jadwal_id' => $jadwal->id,
            'orang_tua_id' => $this->orangTua->id,
            'balita_id' => $this->balita->id,
        ]);

        // Reservasi milik orang tua lain
        $otherOrangTua = OrangTua::factory()->create();
        $otherBalita = Balita::factory()->create(['orang_tua_id' => $otherOrangTua->id]);
        $otherReservasi = Reservasi::factory()->create([
            'jadwal_id' => $jadwal->id,
            'orang_tua_id' => $otherOrangTua->id,
            'balita_id' => $otherBalita->id,
        ]);

        $response = $this->actingAs($this->wargaUser)->get(route('reservasi.index'));

        $response->assertStatus(200);
        $response->assertSee($this->balita->name);
        $response->assertDontSee($otherBalita->name);
    }

    public function test_warga_can_create_reservation_for_own_balita(): void
    {
        $jadwal = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(3)->toDateString(),
            'kuota_jadwal' => 15,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->wargaUser)
            ->postJson(route('reservasi.store'), [
                'jadwal_id' => $jadwal->id,
                'balita_id' => $this->balita->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.balita_id', $this->balita->id)
            ->assertJsonPath('data.jadwal_id', $jadwal->id);

        $this->assertDatabaseHas('reservasi', [
            'jadwal_id' => $jadwal->id,
            'balita_id' => $this->balita->id,
            'orang_tua_id' => $this->orangTua->id,
            'status' => 'dikonfirmasi',
        ]);
    }

    public function test_warga_cannot_reserve_for_other_family_balita(): void
    {
        $jadwal = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(3)->toDateString(),
            'kuota_jadwal' => 15,
            'status' => 'aktif',
        ]);

        $otherOrangTua = OrangTua::factory()->create();
        $otherBalita = Balita::factory()->create(['orang_tua_id' => $otherOrangTua->id]);

        $response = $this->actingAs($this->wargaUser)
            ->postJson(route('reservasi.store'), [
                'jadwal_id' => $jadwal->id,
                'balita_id' => $otherBalita->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['balita_id']);
    }

    public function test_reservation_rejected_when_quota_full(): void
    {
        $jadwal = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(3)->toDateString(),
            'kuota_jadwal' => 1,
            'status' => 'aktif',
        ]);

        // Isi kuota sampai habis
        Reservasi::factory()->create([
            'jadwal_id' => $jadwal->id,
            'status' => 'dikonfirmasi',
        ]);

        $response = $this->actingAs($this->wargaUser)
            ->postJson(route('reservasi.store'), [
                'jadwal_id' => $jadwal->id,
                'balita_id' => $this->balita->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['jadwal_id']);
    }

    public function test_warga_can_reschedule_before_deadline(): void
    {
        $jadwalAwal = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(4)->toDateString(),
            'status' => 'aktif',
        ]);
        $jadwalBaru = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(6)->toDateString(),
            'status' => 'aktif',
        ]);

        $reservasi = Reservasi::factory()->create([
            'jadwal_id' => $jadwalAwal->id,
            'orang_tua_id' => $this->orangTua->id,
            'balita_id' => $this->balita->id,
            'status' => 'dikonfirmasi',
            'batas_reschedule' => now()->addDays(3),
        ]);

        $response = $this->actingAs($this->wargaUser)
            ->patchJson(route('reservasi.reschedule', $reservasi), [
                'new_jadwal_id' => $jadwalBaru->id,
            ]);

        $response->assertStatus(200);
        $this->assertSame($jadwalBaru->id, $reservasi->fresh()->jadwal_id);
    }

    public function test_warga_cannot_reschedule_after_deadline(): void
    {
        $jadwalAwal = JadwalPosyandu::factory()->create(['status' => 'aktif']);
        $jadwalBaru = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(5)->toDateString(),
            'status' => 'aktif',
        ]);

        $reservasi = Reservasi::factory()->expiredReschedule()->create([
            'jadwal_id' => $jadwalAwal->id,
            'orang_tua_id' => $this->orangTua->id,
            'balita_id' => $this->balita->id,
            'status' => 'dikonfirmasi',
        ]);

        $response = $this->actingAs($this->wargaUser)
            ->patchJson(route('reservasi.reschedule', $reservasi), [
                'new_jadwal_id' => $jadwalBaru->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['batas_reschedule']);
    }

    public function test_warga_can_cancel_reservation(): void
    {
        $reservasi = Reservasi::factory()->create([
            'orang_tua_id' => $this->orangTua->id,
            'balita_id' => $this->balita->id,
            'status' => 'dikonfirmasi',
        ]);

        $response = $this->actingAs($this->wargaUser)
            ->patchJson(route('reservasi.cancel', $reservasi));

        $response->assertStatus(200);
        $this->assertSame('dibatalkan', $reservasi->fresh()->status);
    }
}
