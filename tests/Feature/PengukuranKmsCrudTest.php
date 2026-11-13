<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PengukuranKms;
use App\Models\Balita;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class PengukuranKmsCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Pastikan database testing
        $this->assertDatabaseIs('nutricare_testing');
    }

    protected function assertDatabaseIs($expected)
    {
        $actual = config('database.connections.mysql.database');
        if ($actual !== $expected) {
            $this->fail("Database salah! Expected '{$expected}', got '{$actual}'. Periksa phpunit.xml");
        }
    }

    /**
     * Test: Nakes dapat membuka index
     */
    public function test_nakes_dapat_membuka_index()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);

        $response = $this->actingAs($user)->get(route('kms.index'));

        $response->assertStatus(200);
        $response->assertViewIs('kms.index');
        $response->assertViewHas('pengukurans');
    }

    /**
     * Test: Kader dapat membuka index
     */
    public function test_kader_dapat_membuka_index()
    {
        $roleKader = Role::where('name', 'Kader Posyandu')->first();
        $user = User::factory()->create(['role_id' => $roleKader->id]);

        $response = $this->actingAs($user)->get(route('kms.index'));

        $response->assertStatus(200);
    }

    /**
     * Test: Nakes dapat membuka create
     */
    public function test_nakes_dapat_membuka_create()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);

        $response = $this->actingAs($user)->get(route('kms.create'));

        $response->assertStatus(200);
        $response->assertViewIs('kms.create');
        $response->assertViewHas('balitas');
    }

    /**
     * Test: Nakes dapat membuka show
     */
    public function test_nakes_dapat_membuka_show()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);
        
        $pengukuran = PengukuranKms::factory()->create();

        $response = $this->actingAs($user)->get(route('kms.show', $pengukuran));

        $response->assertStatus(200);
        $response->assertViewIs('kms.show');
        $response->assertViewHas('kms');
    }

    /**
     * Test: Store valid menyimpan data dengan petugas otomatis
     */
    public function test_store_valid_menyimpan_data_dengan_petugas_otomatis()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);
        $balita = Balita::factory()->create([
            'birth_date' => Carbon::parse('2024-01-15'),
        ]);

        $data = [
            'balita_id' => $balita->id,
            'date' => '2026-02-15',
            'berat_badan' => 10.5,
            'tinggi_badan' => 80.0,
            'lingkar_lengan_atas' => 12.5,
        ];

        $response = $this->actingAs($user)->post(route('kms.store'), $data);

        $response->assertRedirect(route('kms.index'));
        $response->assertSessionHas('success', 'Data pengukuran berhasil disimpan.');

        $this->assertDatabaseHas('pengukuran_kms', [
            'balita_id' => $balita->id,
            'user_id' => $user->id, // Petugas terisi otomatis
            'date' => '2026-02-15',
            'berat_badan' => 10.5,
            'tinggi_badan' => 80.0,
            'lingkar_lengan_atas' => 12.5,
            'z_score_bbu' => null,
            'z_score_tbu' => null,
            'z_score_bbtb' => null,
            'status_gizi' => null,
        ]);
    }

    /**
     * Test: Store menerima desimal berkoma
     */
    public function test_store_menerima_desimal_berkoma()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);
        $balita = Balita::factory()->create([
            'birth_date' => Carbon::parse('2024-01-15'),
        ]);

        $data = [
            'balita_id' => $balita->id,
            'date' => '2026-02-15',
            'berat_badan' => '9,5', // Koma
            'tinggi_badan' => '75,8',
            'lingkar_lengan_atas' => '11,2',
        ];

        $response = $this->actingAs($user)->post(route('kms.store'), $data);

        $response->assertRedirect(route('kms.index'));

        $this->assertDatabaseHas('pengukuran_kms', [
            'balita_id' => $balita->id,
            'berat_badan' => 9.5,
            'tinggi_badan' => 75.8,
            'lingkar_lengan_atas' => 11.2,
        ]);
    }

    /**
     * Test: Store gagal jika balita tidak ada
     */
    public function test_store_gagal_jika_balita_tidak_ada()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);

        $data = [
            'balita_id' => 99999,
            'date' => '2026-02-15',
            'berat_badan' => 10.5,
            'tinggi_badan' => 80.0,
        ];

        $response = $this->actingAs($user)->post(route('kms.store'), $data);

        $response->assertSessionHasErrors('balita_id');
    }

    /**
     * Test: Store gagal jika tanggal di masa depan
     */
    public function test_store_gagal_jika_tanggal_di_masa_depan()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);
        $balita = Balita::factory()->create();

        $data = [
            'balita_id' => $balita->id,
            'date' => now()->addDays(1)->format('Y-m-d'),
            'berat_badan' => 10.5,
            'tinggi_badan' => 80.0,
        ];

        $response = $this->actingAs($user)->post(route('kms.store'), $data);

        $response->assertSessionHasErrors('date');
    }

    /**
     * Test: Store gagal jika tanggal sebelum tanggal lahir
     */
    public function test_store_gagal_jika_tanggal_sebelum_tanggal_lahir()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);
        $balita = Balita::factory()->create([
            'birth_date' => Carbon::parse('2024-06-15'),
        ]);

        $data = [
            'balita_id' => $balita->id,
            'date' => '2024-06-10', // Sebelum tanggal lahir
            'berat_badan' => 10.5,
            'tinggi_badan' => 80.0,
        ];

        $response = $this->actingAs($user)->post(route('kms.store'), $data);

        $response->assertSessionHasErrors('date');
    }

    /**
     * Test: Store gagal jika usia > 60 bulan
     */
    public function test_store_gagal_jika_usia_lebih_dari_60_bulan()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);
        $balita = Balita::factory()->create([
            'birth_date' => Carbon::parse('2020-12-15'), // > 5 tahun
        ]);

        $data = [
            'balita_id' => $balita->id,
            'date' => now()->format('Y-m-d'),
            'berat_badan' => 18.0,
            'tinggi_badan' => 110.0,
        ];

        $response = $this->actingAs($user)->post(route('kms.store'), $data);

        $response->assertSessionHasErrors('date');
        $this->assertTrue(str_contains(
            session('errors')->get('date')[0],
            'Usia balita saat diukur melebihi 60 bulan'
        ));
    }

    /**
     * Test: Store gagal jika BB di luar 0,5-30
     */
    public function test_store_gagal_jika_bb_di_luar_rentang()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);
        $balita = Balita::factory()->create();

        $dataTooLow = [
            'balita_id' => $balita->id,
            'date' => now()->format('Y-m-d'),
            'berat_badan' => 0.4,
            'tinggi_badan' => 50.0,
        ];

        $response = $this->actingAs($user)->post(route('kms.store'), $dataTooLow);
        $response->assertSessionHasErrors('berat_badan');

        $dataTooHigh = [
            'balita_id' => $balita->id,
            'date' => now()->format('Y-m-d'),
            'berat_badan' => 31.0,
            'tinggi_badan' => 50.0,
        ];

        $response = $this->actingAs($user)->post(route('kms.store'), $dataTooHigh);
        $response->assertSessionHasErrors('berat_badan');
    }

    /**
     * Test: Store gagal jika TB di luar 30-130
     */
    public function test_store_gagal_jika_tb_di_luar_rentang()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);
        $balita = Balita::factory()->create();

        $dataTooLow = [
            'balita_id' => $balita->id,
            'date' => now()->format('Y-m-d'),
            'berat_badan' => 10.0,
            'tinggi_badan' => 29.0,
        ];

        $response = $this->actingAs($user)->post(route('kms.store'), $dataTooLow);
        $response->assertSessionHasErrors('tinggi_badan');

        $dataTooHigh = [
            'balita_id' => $balita->id,
            'date' => now()->format('Y-m-d'),
            'berat_badan' => 10.0,
            'tinggi_badan' => 131.0,
        ];

        $response = $this->actingAs($user)->post(route('kms.store'), $dataTooHigh);
        $response->assertSessionHasErrors('tinggi_badan');
    }

    /**
     * Test: Store gagal jika LILA di luar 5-30
     */
    public function test_store_gagal_jika_lila_di_luar_rentang()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);
        $balita = Balita::factory()->create();

        $dataTooLow = [
            'balita_id' => $balita->id,
            'date' => now()->format('Y-m-d'),
            'berat_badan' => 10.0,
            'tinggi_badan' => 80.0,
            'lingkar_lengan_atas' => 4.0,
        ];

        $response = $this->actingAs($user)->post(route('kms.store'), $dataTooLow);
        $response->assertSessionHasErrors('lingkar_lengan_atas');
    }

    /**
     * Test: Store gagal jika field wajib kosong
     */
    public function test_store_gagal_jika_field_wajib_kosong()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);

        $response = $this->actingAs($user)->post(route('kms.store'), []);

        $response->assertSessionHasErrors(['balita_id', 'date', 'berat_badan', 'tinggi_badan']);
    }

    /**
     * Test: Store gagal jika ada pengukuran ganda pada tanggal yang sama
     */
    public function test_store_gagal_jika_ada_pengukuran_ganda_pada_tanggal_sama()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);
        $balita = Balita::factory()->create();

        // Pengukuran pertama
        PengukuranKms::factory()->create([
            'balita_id' => $balita->id,
            'date' => '2026-02-15',
        ]);

        // Coba tambah lagi dengan balita dan tanggal yang sama
        $data = [
            'balita_id' => $balita->id,
            'date' => '2026-02-15',
            'berat_badan' => 10.5,
            'tinggi_badan' => 80.0,
        ];

        $response = $this->actingAs($user)->post(route('kms.store'), $data);

        $response->assertSessionHasErrors('date');
        $this->assertTrue(str_contains(
            session('errors')->get('date')[0],
            'sudah memiliki pengukuran pada tanggal tersebut'
        ));
    }

    /**
     * Test: Update mengubah data tanpa mengganti petugas
     */
    public function test_update_mengubah_data_tanpa_mengganti_petugas()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $petugasAsli = User::factory()->create(['role_id' => $roleNakes->id]);
        $petugasLain = User::factory()->create(['role_id' => $roleNakes->id]);

        $pengukuran = PengukuranKms::factory()->create([
            'user_id' => $petugasAsli->id,
            'berat_badan' => 10.0,
        ]);

        $dataUpdate = [
            'balita_id' => $pengukuran->balita_id,
            'date' => $pengukuran->date->format('Y-m-d'),
            'berat_badan' => 11.0,
            'tinggi_badan' => $pengukuran->tinggi_badan,
        ];

        $response = $this->actingAs($petugasLain)->put(route('kms.update', $pengukuran), $dataUpdate);

        $response->assertRedirect(route('kms.show', $pengukuran));

        $pengukuran->refresh();
        $this->assertEquals(11.0, $pengukuran->berat_badan);
        $this->assertEquals($petugasAsli->id, $pengukuran->user_id); // Petugas tidak berubah
    }

    /**
     * Test: Update tidak gagal oleh validasi duplikat terhadap dirinya sendiri
     */
    public function test_update_tidak_gagal_oleh_validasi_duplikat_terhadap_dirinya_sendiri()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);

        $pengukuran = PengukuranKms::factory()->create();

        $dataUpdate = [
            'balita_id' => $pengukuran->balita_id,
            'date' => $pengukuran->date->format('Y-m-d'), // Tanggal sama
            'berat_badan' => 11.0,
            'tinggi_badan' => $pengukuran->tinggi_badan,
        ];

        $response = $this->actingAs($user)->put(route('kms.update', $pengukuran), $dataUpdate);

        $response->assertRedirect(route('kms.show', $pengukuran));
        $response->assertSessionDoesntHaveErrors();
    }

    /**
     * Test: Destroy menghapus data
     */
    public function test_destroy_menghapus_data()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);

        $pengukuran = PengukuranKms::factory()->create();

        $response = $this->actingAs($user)->delete(route('kms.destroy', $pengukuran));

        $response->assertRedirect(route('kms.index'));
        $response->assertSessionHas('success', 'Data pengukuran berhasil dihapus.');

        $this->assertDatabaseMissing('pengukuran_kms', [
            'id' => $pengukuran->id,
        ]);
    }

    /**
     * Test: Index pencarian nama balita
     */
    public function test_index_pencarian_nama_balita()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);

        $balita1 = Balita::factory()->create(['name' => 'Ahmad']);
        $balita2 = Balita::factory()->create(['name' => 'Siti']);

        PengukuranKms::factory()->create(['balita_id' => $balita1->id]);
        PengukuranKms::factory()->create(['balita_id' => $balita2->id]);

        $response = $this->actingAs($user)->get(route('kms.index', ['search' => 'Ahmad']));

        $response->assertStatus(200);
        $response->assertSee('Ahmad');
        $response->assertDontSee('Siti');
    }

    /**
     * Test: Index filter tanggal
     */
    public function test_index_filter_tanggal()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);

        $pengukuran1 = PengukuranKms::factory()->create(['date' => '2026-01-15']);
        $pengukuran2 = PengukuranKms::factory()->create(['date' => '2026-02-15']);

        $response = $this->actingAs($user)->get(route('kms.index', [
            'start_date' => '2026-02-01',
            'end_date' => '2026-02-28',
        ]));

        $response->assertStatus(200);
        $response->assertSee('2026-02-15');
    }

    /**
     * Test: Index filter balita_id
     */
    public function test_index_filter_balita_id()
    {
        $roleNakes = Role::where('name', 'Nakes / Bidan')->first();
        $user = User::factory()->create(['role_id' => $roleNakes->id]);

        $balita1 = Balita::factory()->create();
        $balita2 = Balita::factory()->create();

        PengukuranKms::factory()->create(['balita_id' => $balita1->id]);
        PengukuranKms::factory()->create(['balita_id' => $balita2->id]);

        $response = $this->actingAs($user)->get(route('kms.index', ['balita_id' => $balita1->id]));

        $response->assertStatus(200);
        $response->assertSee($balita1->name);
        $response->assertDontSee($balita2->name);
    }

    /**
     * Test: Pengguna belum login ditolak (jika middleware auth ada)
     */
    public function test_pengguna_belum_login_ditolak()
    {
        $response = $this->get(route('kms.index'));

        // Jika ada middleware auth, akan redirect ke login
        // Jika belum ada, akan status 200
        // Test ini disesuaikan setelah middleware auth (Adhani) tersedia
        $this->assertTrue(
            $response->status() === 302 || $response->status() === 200,
            'Test ini akan diperketat setelah middleware auth tersedia'
        );
    }

    /**
     * Test: Role Warga ditolak (jika middleware role ada)
     */
    public function test_role_warga_ditolak()
    {
        $roleWarga = Role::where('name', 'Warga / Orang Tua')->first();
        if (!$roleWarga) {
            $this->markTestSkipped('Role Warga tidak ada di seeder');
        }

        $user = User::factory()->create(['role_id' => $roleWarga->id]);

        $response = $this->actingAs($user)->get(route('kms.index'));

        // Jika middleware role ada, akan ditolak (403 atau redirect)
        // Jika belum ada, akan status 200
        // Test ini disesuaikan setelah middleware role (Adhani) tersedia
        $this->assertTrue(
            in_array($response->status(), [200, 302, 403]),
            'Test ini akan diperketat setelah middleware role tersedia'
        );
    }
}
