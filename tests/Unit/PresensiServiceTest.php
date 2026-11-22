<?php

namespace Tests\Unit;

use App\Models\Balita;
use App\Models\JadwalPosyandu;
use App\Models\OrangTua;
use App\Models\Presensi;
use App\Models\Reservasi;
use App\Models\Role;
use App\Models\User;
use App\Services\PresensiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PresensiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PresensiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PresensiService::class);

        // Pastikan role dasar ada
        Role::firstOrCreate(['name_role' => 'Kader Posyandu'], ['description' => 'Kader']);
        Role::firstOrCreate(['name_role' => 'Admin / Kepala Puskesmas'], ['description' => 'Admin']);
        Role::firstOrCreate(['name_role' => 'Warga / Orang Tua'], ['description' => 'Warga']);
    }

    public function test_check_in_valid_qr_creates_presensi(): void
    {
        $jadwal = JadwalPosyandu::factory()->create(['status' => 'aktif']);
        $orangTua = OrangTua::factory()->create();
        $balita = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'qr_code' => 'NC-VALID001',
        ]);

        $result = $this->service->checkInViaQr($jadwal->id, 'NC-VALID001');

        $this->assertSame('success', $result['status']);
        $this->assertTrue($result['is_new']);
        $this->assertDatabaseHas('presensi', [
            'jadwal_id' => $jadwal->id,
            'balita_id' => $balita->id,
            'presence_status' => 'hadir',
        ]);
    }

    public function test_check_in_invalid_qr_throws_validation_exception(): void
    {
        $jadwal = JadwalPosyandu::factory()->create(['status' => 'aktif']);

        $this->expectException(ValidationException::class);
        $this->service->checkInViaQr($jadwal->id, 'NC-NONEXISTENT');
    }

    public function test_check_in_cancelled_jadwal_throws_validation_exception(): void
    {
        $jadwal = JadwalPosyandu::factory()->dibatalkan()->create();
        $balita = Balita::factory()->create(['qr_code' => 'NC-VALID002']);

        $this->expectException(ValidationException::class);
        $this->service->checkInViaQr($jadwal->id, 'NC-VALID002');
    }

    public function test_check_in_duplicate_returns_idempotent_response_without_creating_new_row(): void
    {
        $jadwal = JadwalPosyandu::factory()->create(['status' => 'aktif']);
        $balita = Balita::factory()->create(['qr_code' => 'NC-VALID003']);

        // First check-in
        $res1 = $this->service->checkInViaQr($jadwal->id, 'NC-VALID003');
        $this->assertTrue($res1['is_new']);

        // Second check-in (repeated scan)
        $res2 = $this->service->checkInViaQr($jadwal->id, 'NC-VALID003');
        $this->assertSame('already_checked_in', $res2['status']);
        $this->assertFalse($res2['is_new']);

        // Verify only 1 row exists
        $this->assertSame(1, Presensi::where('jadwal_id', $jadwal->id)->where('balita_id', $balita->id)->count());
    }

    public function test_check_in_marks_active_reservation_as_selesai(): void
    {
        $jadwal = JadwalPosyandu::factory()->create(['status' => 'aktif']);
        $orangTua = OrangTua::factory()->create();
        $balita = Balita::factory()->create([
            'orang_tua_id' => $orangTua->id,
            'qr_code' => 'NC-VALID004',
        ]);

        $reservasi = Reservasi::factory()->create([
            'jadwal_id' => $jadwal->id,
            'orang_tua_id' => $orangTua->id,
            'balita_id' => $balita->id,
            'status' => 'dikonfirmasi',
        ]);

        $this->service->checkInViaQr($jadwal->id, 'NC-VALID004');

        $this->assertSame('selesai', $reservasi->fresh()->status);
    }

    public function test_update_status_presensi(): void
    {
        $presensi = Presensi::factory()->create(['presence_status' => 'hadir']);

        $updated = $this->service->updateStatus($presensi, 'tidak_hadir');

        $this->assertSame('tidak_hadir', $updated->presence_status);
        $this->assertDatabaseHas('presensi', [
            'id' => $presensi->id,
            'presence_status' => 'tidak_hadir',
        ]);
    }

    public function test_delete_presensi(): void
    {
        $presensi = Presensi::factory()->create();

        $result = $this->service->deletePresensi($presensi);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('presensi', ['id' => $presensi->id]);
    }

    public function test_rekap_presensi_calculation(): void
    {
        $jadwal = JadwalPosyandu::factory()->create(['kuota_jadwal' => 30]);
        Presensi::factory()->count(5)->create(['jadwal_id' => $jadwal->id, 'presence_status' => 'hadir']);
        Presensi::factory()->count(2)->create(['jadwal_id' => $jadwal->id, 'presence_status' => 'tidak_hadir']);
        Reservasi::factory()->count(10)->create(['jadwal_id' => $jadwal->id, 'status' => 'dikonfirmasi']);

        $rekap = $this->service->getRekapPresensi($jadwal->id);

        $this->assertSame(5, $rekap['hadir']);
        $this->assertSame(2, $rekap['tidak_hadir']);
        $this->assertSame(7, $rekap['total_presensi']);
        $this->assertSame(10, $rekap['total_reservasi']);
        $this->assertSame(30, $rekap['kuota']);
        $this->assertEquals(50.0, $rekap['persentase_kehadiran']);
    }
}
