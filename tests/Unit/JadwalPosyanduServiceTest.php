<?php

namespace Tests\Unit;

use App\Models\JadwalPosyandu;
use App\Models\Role;
use App\Models\User;
use App\Services\JadwalPosyanduService;
use App\Contracts\Paket4\NakesLookup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Tests\Support\Paket4\FakeNakesLookup;
use Tests\TestCase;

class JadwalPosyanduServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected JadwalPosyanduService $service;
    protected User $nakesA;
    protected User $nakesB;
    protected User $nakesNonaktif;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(JadwalPosyanduService::class);

        $nakesRole = Role::firstOrCreate(['name_role' => 'Nakes / Bidan']);

        $this->nakesA = User::firstOrCreate(
            ['username' => 'test_nakes_a'],
            [
                'role_id' => $nakesRole->id,
                'name' => 'Bidan Aisyah Test',
                'email' => 'nakes_a_test@example.com',
                'password' => bcrypt('password'),
                'active_status' => true,
            ]
        );

        $this->nakesB = User::firstOrCreate(
            ['username' => 'test_nakes_b'],
            [
                'role_id' => $nakesRole->id,
                'name' => 'Bidan Budi Test',
                'email' => 'nakes_b_test@example.com',
                'password' => bcrypt('password'),
                'active_status' => true,
            ]
        );

        $this->nakesNonaktif = User::firstOrCreate(
            ['username' => 'test_nakes_nonaktif'],
            [
                'role_id' => $nakesRole->id,
                'name' => 'Bidan Nonaktif Test',
                'email' => 'nakes_nonaktif_test@example.com',
                'password' => bcrypt('password'),
                'active_status' => false,
            ]
        );
    }

    public function test_validate_interval_throws_when_end_is_before_or_equal_to_start(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->validateInterval('10:00:00', '09:00:00');
    }

    public function test_validate_interval_throws_when_end_equals_start(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->validateInterval('10:00:00', '10:00:00');
    }

    public function test_create_jadwal_success(): void
    {
        $jadwal = $this->service->createJadwal([
            'user_id' => $this->nakesA->id,
            'judul' => 'Posyandu Mawar',
            'date' => '2026-11-20',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'location' => 'Balai RW 01',
            'kuota_jadwal' => 30,
        ]);

        $this->assertDatabaseHas('jadwal_posyandu', [
            'id' => $jadwal->id,
            'judul' => 'Posyandu Mawar',
            'status' => 'aktif',
        ]);
    }

    public function test_create_jadwal_rejects_inactive_nakes(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->createJadwal([
            'user_id' => $this->nakesNonaktif->id,
            'judul' => 'Posyandu Nonaktif',
            'date' => '2026-11-20',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'location' => 'Balai RW 01',
            'kuota_jadwal' => 30,
        ]);
    }

    public function test_create_jadwal_rejects_overlapping_interval_for_same_nakes(): void
    {
        $this->service->createJadwal([
            'user_id' => $this->nakesA->id,
            'judul' => 'Jadwal Pertama',
            'date' => '2026-11-20',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '11:00:00',
            'location' => 'Balai RW 01',
            'kuota_jadwal' => 30,
        ]);

        $this->expectException(ValidationException::class);

        // Bentrok sebagian (09:30 - 10:30 bertabrakan dengan 09:00 - 11:00)
        $this->service->createJadwal([
            'user_id' => $this->nakesA->id,
            'judul' => 'Jadwal Bentrok',
            'date' => '2026-11-20',
            'jam_mulai' => '09:30:00',
            'jam_selesai' => '10:30:00',
            'location' => 'Balai RW 02',
            'kuota_jadwal' => 20,
        ]);
    }

    public function test_create_jadwal_allows_adjacent_interval_for_same_nakes(): void
    {
        // Jadwal 1: 08:00 - 10:00
        $j1 = $this->service->createJadwal([
            'user_id' => $this->nakesA->id,
            'judul' => 'Sesi Pagi',
            'date' => '2026-11-21',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'location' => 'Balai RW 01',
            'kuota_jadwal' => 30,
        ]);

        // Jadwal 2: 10:00 - 12:00 (tepat bersambung)
        $j2 = $this->service->createJadwal([
            'user_id' => $this->nakesA->id,
            'judul' => 'Sesi Siang',
            'date' => '2026-11-21',
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '12:00:00',
            'location' => 'Balai RW 01',
            'kuota_jadwal' => 30,
        ]);

        $this->assertInstanceOf(JadwalPosyandu::class, $j1);
        $this->assertInstanceOf(JadwalPosyandu::class, $j2);
    }

    public function test_create_jadwal_allows_same_time_for_different_nakes(): void
    {
        $j1 = $this->service->createJadwal([
            'user_id' => $this->nakesA->id,
            'judul' => 'Posyandu A',
            'date' => '2026-11-22',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '11:00:00',
            'location' => 'Balai RW 01',
            'kuota_jadwal' => 30,
        ]);

        $j2 = $this->service->createJadwal([
            'user_id' => $this->nakesB->id,
            'judul' => 'Posyandu B',
            'date' => '2026-11-22',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '11:00:00',
            'location' => 'Balai RW 02',
            'kuota_jadwal' => 30,
        ]);

        $this->assertInstanceOf(JadwalPosyandu::class, $j1);
        $this->assertInstanceOf(JadwalPosyandu::class, $j2);
    }

    public function test_update_jadwal_without_changing_interval_does_not_self_conflict(): void
    {
        $jadwal = $this->service->createJadwal([
            'user_id' => $this->nakesA->id,
            'judul' => 'Judul Awal',
            'date' => '2026-11-23',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'location' => 'Balai RW 01',
            'kuota_jadwal' => 30,
        ]);

        $updated = $this->service->updateJadwal($jadwal, [
            'judul' => 'Judul Baru Diperbarui',
            'location' => 'Balai RW 05',
        ]);

        $this->assertEquals('Judul Baru Diperbarui', $updated->judul);
        $this->assertEquals('Balai RW 05', $updated->location);
    }

    public function test_cancelled_schedule_does_not_block_time_interval(): void
    {
        $jadwal1 = $this->service->createJadwal([
            'user_id' => $this->nakesA->id,
            'judul' => 'Akan Dibatalkan',
            'date' => '2026-11-24',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'location' => 'Balai RW 01',
            'kuota_jadwal' => 30,
        ]);

        $this->service->cancelJadwal($jadwal1);

        // Setelah dibatalkan, jadwal baru di jam yang sama untuk nakes A harus berhasil dibuat
        $jadwal2 = $this->service->createJadwal([
            'user_id' => $this->nakesA->id,
            'judul' => 'Jadwal Pengganti',
            'date' => '2026-11-24',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'location' => 'Balai RW 01',
            'kuota_jadwal' => 30,
        ]);

        $this->assertEquals('aktif', $jadwal2->status);
    }

    public function test_calendar_feed_returns_formatted_events(): void
    {
        $this->service->createJadwal([
            'user_id' => $this->nakesA->id,
            'judul' => 'Event Kalender',
            'date' => '2026-11-25',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'location' => 'Balai RW 01',
            'kuota_jadwal' => 30,
        ]);

        $feed = $this->service->getCalendarFeed('2026-11-01', '2026-11-30');

        $this->assertNotEmpty($feed);
        $firstEvent = $feed->firstWhere('title', 'Event Kalender');
        $this->assertNotNull($firstEvent);
        $this->assertEquals('2026-11-25T08:00:00', $firstEvent['start']);
        $this->assertEquals('2026-11-25T10:00:00', $firstEvent['end']);
    }
}

