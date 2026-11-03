<?php

namespace Tests\Feature;

use App\Models\JadwalPosyandu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class JadwalPosyanduCrudTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $kader;
    protected User $warga;
    protected User $nakes;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name_role' => 'Admin / Kepala Puskesmas']);
        $nakesRole = Role::firstOrCreate(['name_role' => 'Nakes / Bidan']);
        $kaderRole = Role::firstOrCreate(['name_role' => 'Kader Posyandu']);
        $wargaRole = Role::firstOrCreate(['name_role' => 'Warga / Orang Tua']);

        $this->admin = User::firstOrCreate(
            ['username' => 'test_admin_pbi11'],
            [
                'role_id' => $adminRole->id,
                'name' => 'Admin Test PBI 11',
                'email' => 'admin_pbi11@example.com',
                'password' => bcrypt('password'),
                'active_status' => true,
            ]
        );

        $this->nakes = User::firstOrCreate(
            ['username' => 'test_nakes_pbi11'],
            [
                'role_id' => $nakesRole->id,
                'name' => 'Bidan PBI 11',
                'email' => 'nakes_pbi11@example.com',
                'password' => bcrypt('password'),
                'active_status' => true,
            ]
        );

        $this->kader = User::firstOrCreate(
            ['username' => 'test_kader_pbi11'],
            [
                'role_id' => $kaderRole->id,
                'name' => 'Kader Test PBI 11',
                'email' => 'kader_pbi11@example.com',
                'password' => bcrypt('password'),
                'active_status' => true,
            ]
        );

        $this->warga = User::firstOrCreate(
            ['username' => 'test_warga_pbi11'],
            [
                'role_id' => $wargaRole->id,
                'name' => 'Warga Test PBI 11',
                'email' => 'warga_pbi11@example.com',
                'password' => bcrypt('password'),
                'active_status' => true,
            ]
        );
    }

    public function test_warga_cannot_access_create_form(): void
    {
        $response = $this->actingAs($this->warga)->get(route('jadwal.create'));
        $response->assertStatus(403);
    }

    public function test_kader_and_admin_can_access_create_form(): void
    {
        $response = $this->actingAs($this->kader)->get(route('jadwal.create'));
        $response->assertStatus(200);

        $responseAdmin = $this->actingAs($this->admin)->get(route('jadwal.create'));
        $responseAdmin->assertStatus(200);
    }

    public function test_store_creates_schedule_and_redirects(): void
    {
        $payload = [
            'user_id' => $this->nakes->id,
            'judul' => 'Posyandu Cempaka Putih',
            'date' => '2026-11-28',
            'jam_mulai' => '08:30',
            'jam_selesai' => '11:00',
            'location' => 'Balai Warga RT 03',
            'kuota_jadwal' => 35,
        ];

        $response = $this->actingAs($this->kader)->post(route('jadwal.store'), $payload);

        $response->assertRedirect(route('jadwal.index'));
        $this->assertDatabaseHas('jadwal_posyandu', [
            'judul' => 'Posyandu Cempaka Putih',
            'kuota_jadwal' => 35,
            'status' => 'aktif',
        ]);
    }

    public function test_store_via_ajax_returns_json_201(): void
    {
        $payload = [
            'user_id' => $this->nakes->id,
            'judul' => 'Posyandu Kenanga Indah',
            'date' => '2026-11-29',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'location' => 'Pos RW 04',
            'kuota_jadwal' => 25,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('jadwal.store'), $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('data.judul', 'Posyandu Kenanga Indah');
    }

    public function test_store_rejects_conflicting_interval_with_422(): void
    {
        JadwalPosyandu::create([
            'user_id' => $this->nakes->id,
            'judul' => 'Jadwal Sudah Ada',
            'date' => '2026-11-30',
            'hour' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'location' => 'Balai RW 01',
            'kuota_jadwal' => 30,
            'status' => 'aktif',
        ]);

        $payload = [
            'user_id' => $this->nakes->id,
            'judul' => 'Jadwal Bentrok',
            'date' => '2026-11-30',
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'location' => 'Balai RW 02',
            'kuota_jadwal' => 20,
        ];

        $response = $this->actingAs($this->kader)
            ->postJson(route('jadwal.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['jam_mulai']);
    }

    public function test_calendar_feed_endpoint_returns_json_events(): void
    {
        JadwalPosyandu::create([
            'user_id' => $this->nakes->id,
            'judul' => 'Jadwal Kalender Feed',
            'date' => '2026-11-15',
            'hour' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'location' => 'Balai RW 01',
            'kuota_jadwal' => 30,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->warga)->getJson(route('jadwal.feed', [
            'start' => '2026-11-01',
            'end' => '2026-11-30',
        ]));

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Jadwal Kalender Feed']);
    }

    public function test_cancel_endpoint_cancels_schedule(): void
    {
        $jadwal = JadwalPosyandu::create([
            'user_id' => $this->nakes->id,
            'judul' => 'Jadwal Mau Dibatalkan',
            'date' => '2026-11-16',
            'hour' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'location' => 'Balai RW 01',
            'kuota_jadwal' => 30,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->kader)
            ->patch(route('jadwal.cancel', $jadwal));

        $response->assertRedirect(route('jadwal.index'));
        $this->assertEquals('dibatalkan', $jadwal->fresh()->status);
    }
}

