<?php

namespace Tests\Unit;

use App\Models\Balita;
use App\Models\JadwalPosyandu;
use App\Models\OrangTua;
use App\Models\Reservasi;
use App\Models\Role;
use App\Models\User;
use App\Services\ReservasiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReservasiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReservasiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ReservasiService::class);

        Role::firstOrCreate(['name_role' => 'Kader Posyandu'], ['description' => 'Kader']);
        Role::firstOrCreate(['name_role' => 'Admin / Kepala Puskesmas'], ['description' => 'Admin']);
        Role::firstOrCreate(['name_role' => 'Warga / Orang Tua'], ['description' => 'Warga']);
    }

    public function test_create_reservasi_success(): void
    {
        $jadwal = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(3)->toDateString(),
            'kuota_jadwal' => 10,
            'status' => 'aktif',
        ]);
        $wargaRole = Role::where('name_role', 'Warga / Orang Tua')->first();
        $user = User::factory()->create(['role_id' => $wargaRole->id]);
        $orangTua = OrangTua::factory()->create(['user_id' => $user->id]);
        $balita = Balita::factory()->create(['orang_tua_id' => $orangTua->id]);

        $reservasi = $this->service->createReservasi([
            'jadwal_id' => $jadwal->id,
            'balita_id' => $balita->id,
        ], $user);

        $this->assertInstanceOf(Reservasi::class, $reservasi);
        $this->assertSame('dikonfirmasi', $reservasi->status);
        $this->assertSame($jadwal->id, $reservasi->jadwal_id);
        $this->assertSame($balita->id, $reservasi->balita_id);
        $this->assertSame($orangTua->id, $reservasi->orang_tua_id);
        $this->assertNotNull($reservasi->batas_reschedule);
    }

    public function test_create_reservasi_throws_when_quota_full(): void
    {
        $jadwal = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(3)->toDateString(),
            'kuota_jadwal' => 2,
            'status' => 'aktif',
        ]);

        // Isi kuota hingga penuh (2 slot)
        Reservasi::factory()->count(2)->create([
            'jadwal_id' => $jadwal->id,
            'status' => 'dikonfirmasi',
        ]);

        $wargaRole = Role::where('name_role', 'Warga / Orang Tua')->first();
        $user = User::factory()->create(['role_id' => $wargaRole->id]);
        $orangTua = OrangTua::factory()->create(['user_id' => $user->id]);
        $balita = Balita::factory()->create(['orang_tua_id' => $orangTua->id]);

        $this->expectException(ValidationException::class);
        $this->service->createReservasi([
            'jadwal_id' => $jadwal->id,
            'balita_id' => $balita->id,
        ], $user);
    }

    public function test_create_reservasi_throws_on_duplicate_active_reservation(): void
    {
        $jadwal = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(3)->toDateString(),
            'kuota_jadwal' => 10,
            'status' => 'aktif',
        ]);

        $wargaRole = Role::where('name_role', 'Warga / Orang Tua')->first();
        $user = User::factory()->create(['role_id' => $wargaRole->id]);
        $orangTua = OrangTua::factory()->create(['user_id' => $user->id]);
        $balita = Balita::factory()->create(['orang_tua_id' => $orangTua->id]);

        // Reservasi pertama
        $this->service->createReservasi([
            'jadwal_id' => $jadwal->id,
            'balita_id' => $balita->id,
        ], $user);

        // Reservasi kedua untuk balita yang sama di jadwal yang sama harus ditolak
        $this->expectException(ValidationException::class);
        $this->service->createReservasi([
            'jadwal_id' => $jadwal->id,
            'balita_id' => $balita->id,
        ], $user);
    }

    public function test_warga_cannot_reserve_for_other_parent_balita(): void
    {
        $jadwal = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(3)->toDateString(),
            'kuota_jadwal' => 10,
            'status' => 'aktif',
        ]);

        $wargaRole = Role::where('name_role', 'Warga / Orang Tua')->first();
        $user = User::factory()->create(['role_id' => $wargaRole->id]);
        OrangTua::factory()->create(['user_id' => $user->id]);

        // Balita milik orang tua lain
        $otherOrangTua = OrangTua::factory()->create();
        $otherBalita = Balita::factory()->create(['orang_tua_id' => $otherOrangTua->id]);

        $this->expectException(ValidationException::class);
        $this->service->createReservasi([
            'jadwal_id' => $jadwal->id,
            'balita_id' => $otherBalita->id,
        ], $user);
    }

    public function test_reschedule_success_atomically_moves_slot(): void
    {
        $jadwalAwal = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(5)->toDateString(),
            'hour' => '09:00:00',
            'kuota_jadwal' => 10,
            'status' => 'aktif',
        ]);
        $jadwalTujuan = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(7)->toDateString(),
            'hour' => '09:00:00',
            'kuota_jadwal' => 10,
            'status' => 'aktif',
        ]);

        $reservasi = Reservasi::factory()->create([
            'jadwal_id' => $jadwalAwal->id,
            'status' => 'dikonfirmasi',
            'batas_reschedule' => now()->addDays(4),
        ]);

        $updated = $this->service->reschedule($reservasi, $jadwalTujuan->id);

        $this->assertSame($jadwalTujuan->id, $updated->jadwal_id);
        $this->assertDatabaseHas('reservasi', [
            'id' => $reservasi->id,
            'jadwal_id' => $jadwalTujuan->id,
        ]);
    }

    public function test_reschedule_fails_when_target_schedule_is_full_and_leaves_original_intact(): void
    {
        $jadwalAwal = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(5)->toDateString(),
            'kuota_jadwal' => 10,
            'status' => 'aktif',
        ]);
        $jadwalPenuh = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(7)->toDateString(),
            'kuota_jadwal' => 1,
            'status' => 'aktif',
        ]);

        // Jadwal penuh terisi 1 slot
        Reservasi::factory()->create([
            'jadwal_id' => $jadwalPenuh->id,
            'status' => 'dikonfirmasi',
        ]);

        $reservasi = Reservasi::factory()->create([
            'jadwal_id' => $jadwalAwal->id,
            'status' => 'dikonfirmasi',
            'batas_reschedule' => now()->addDays(4),
        ]);

        try {
            $this->service->reschedule($reservasi, $jadwalPenuh->id);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            // Asal tetap utuh di jadwal awal
            $this->assertSame($jadwalAwal->id, $reservasi->fresh()->jadwal_id);
            $this->assertSame('dikonfirmasi', $reservasi->fresh()->status);
        }
    }

    public function test_reschedule_fails_after_deadline(): void
    {
        $jadwal = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(1)->toDateString(),
            'status' => 'aktif',
        ]);
        $target = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(4)->toDateString(),
            'status' => 'aktif',
        ]);

        $reservasi = Reservasi::factory()->expiredReschedule()->create([
            'jadwal_id' => $jadwal->id,
            'status' => 'dikonfirmasi',
        ]);

        $this->expectException(ValidationException::class);
        $this->service->reschedule($reservasi, $target->id);
    }

    public function test_cancel_reservasi_frees_up_quota(): void
    {
        $jadwal = JadwalPosyandu::factory()->create([
            'date' => now()->addDays(3)->toDateString(),
            'kuota_jadwal' => 5,
            'status' => 'aktif',
        ]);

        $reservasi = Reservasi::factory()->create([
            'jadwal_id' => $jadwal->id,
            'status' => 'dikonfirmasi',
        ]);

        $this->assertSame(4, $jadwal->sisa_kuota);

        $this->service->cancelReservasi($reservasi);

        $this->assertSame('dibatalkan', $reservasi->fresh()->status);
        $this->assertSame(5, $jadwal->fresh()->sisa_kuota);
    }
}
