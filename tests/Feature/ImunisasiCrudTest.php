<?php

namespace Tests\Feature;

use App\Models\Balita;
use App\Models\Imunisasi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImunisasiCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $nakes;
    protected $kader;
    protected $warga;
    protected $balita;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat roles
        $roleNakes = Role::create(['name_role' => 'Nakes / Bidan']);
        $roleKader = Role::create(['name_role' => 'Kader Posyandu']);
        $roleWarga = Role::create(['name_role' => 'Warga / Orang Tua']);

        // Buat users
        $this->nakes = User::factory()->create(['role_id' => $roleNakes->id]);
        $this->kader = User::factory()->create(['role_id' => $roleKader->id]);
        $this->warga = User::factory()->create(['role_id' => $roleWarga->id]);

        // Buat balita
        $this->balita = Balita::factory()->create();
    }

    /** @test */
    public function nakes_dapat_membuka_index()
    {
        $response = $this->actingAs($this->nakes)->get(route('imunisasi.index'));
        $response->assertOk();
        $response->assertViewIs('imunisasi.index');
    }

    /** @test */
    public function kader_dapat_membuka_index()
    {
        $response = $this->actingAs($this->kader)->get(route('imunisasi.index'));
        $response->assertOk();
    }

    /** @test */
    public function nakes_dapat_membuka_create()
    {
        $response = $this->actingAs($this->nakes)->get(route('imunisasi.create'));
        $response->assertOk();
        $response->assertViewIs('imunisasi.create');
    }

    /** @test */
    public function nakes_dapat_membuka_show()
    {
        $imunisasi = Imunisasi::factory()->create([
            'balita_id' => $this->balita->id,
            'user_id' => $this->nakes->id,
        ]);

        $response = $this->actingAs($this->nakes)->get(route('imunisasi.show', $imunisasi));
        $response->assertOk();
        $response->assertViewIs('imunisasi.show');
    }

    /** @test */
    public function store_berhasil_dengan_data_valid_dan_petugas_terisi_otomatis()
    {
        $data = [
            'balita_id' => $this->balita->id,
            'jenis_vaksin' => 'BCG',
            'tanggal' => now()->subDays(5)->format('Y-m-d'),
            'no_batch' => 'BTC-20261110',
        ];

        $response = $this->actingAs($this->nakes)->post(route('imunisasi.store'), $data);

        $response->assertRedirect(route('imunisasi.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('imunisasi', [
            'balita_id' => $this->balita->id,
            'user_id' => $this->nakes->id, // Petugas diisi otomatis dari auth
            'jenis_vaksin' => 'BCG',
            'no_batch' => 'BTC-20261110',
        ]);
    }

    /** @test */
    public function store_gagal_balita_tidak_ada()
    {
        $data = [
            'balita_id' => 999999,
            'jenis_vaksin' => 'BCG',
            'tanggal' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->nakes)->post(route('imunisasi.store'), $data);
        $response->assertSessionHasErrors('balita_id');
    }

    /** @test */
    public function store_gagal_jenis_vaksin_tidak_valid()
    {
        $data = [
            'balita_id' => $this->balita->id,
            'jenis_vaksin' => 'Vaksin Tidak Ada',
            'tanggal' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->nakes)->post(route('imunisasi.store'), $data);
        $response->assertSessionHasErrors('jenis_vaksin');
    }

    /** @test */
    public function store_gagal_tanggal_di_masa_depan()
    {
        $data = [
            'balita_id' => $this->balita->id,
            'jenis_vaksin' => 'BCG',
            'tanggal' => now()->addDays(1)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->nakes)->post(route('imunisasi.store'), $data);
        $response->assertSessionHasErrors('tanggal');
    }

    /** @test */
    public function store_gagal_tanggal_sebelum_tanggal_lahir()
    {
        $data = [
            'balita_id' => $this->balita->id,
            'jenis_vaksin' => 'BCG',
            'tanggal' => $this->balita->birth_date->subDays(1)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->nakes)->post(route('imunisasi.store'), $data);
        $response->assertSessionHasErrors('tanggal');
    }

    /** @test */
    public function store_gagal_no_batch_lebih_dari_50_karakter()
    {
        $data = [
            'balita_id' => $this->balita->id,
            'jenis_vaksin' => 'BCG',
            'tanggal' => now()->format('Y-m-d'),
            'no_batch' => str_repeat('A', 51),
        ];

        $response = $this->actingAs($this->nakes)->post(route('imunisasi.store'), $data);
        $response->assertSessionHasErrors('no_batch');
    }

    /** @test */
    public function store_gagal_field_wajib_kosong()
    {
        $response = $this->actingAs($this->nakes)->post(route('imunisasi.store'), []);
        $response->assertSessionHasErrors(['balita_id', 'jenis_vaksin', 'tanggal']);
    }

    /** @test */
    public function update_mengubah_data_tetapi_tidak_mengganti_petugas()
    {
        $imunisasi = Imunisasi::factory()->create([
            'balita_id' => $this->balita->id,
            'user_id' => $this->nakes->id,
            'jenis_vaksin' => 'BCG',
        ]);

        $balitaBaru = Balita::factory()->create();

        $data = [
            'balita_id' => $balitaBaru->id,
            'jenis_vaksin' => 'Polio 1',
            'tanggal' => now()->subDays(3)->format('Y-m-d'),
            'no_batch' => 'NEW-BATCH',
        ];

        $response = $this->actingAs($this->kader)->put(route('imunisasi.update', $imunisasi), $data);

        $response->assertRedirect(route('imunisasi.index'));

        $imunisasi->refresh();
        $this->assertEquals('Polio 1', $imunisasi->jenis_vaksin);
        $this->assertEquals($balitaBaru->id, $imunisasi->balita_id);
        $this->assertEquals($this->nakes->id, $imunisasi->user_id); // Petugas tetap
    }

    /** @test */
    public function destroy_menghapus_data()
    {
        $imunisasi = Imunisasi::factory()->create([
            'balita_id' => $this->balita->id,
            'user_id' => $this->nakes->id,
        ]);

        $response = $this->actingAs($this->nakes)->delete(route('imunisasi.destroy', $imunisasi));

        $response->assertRedirect(route('imunisasi.index'));
        $this->assertDatabaseMissing('imunisasi', ['id' => $imunisasi->id]);
    }

    /** @test */
    public function pencarian_nama_balita_bekerja()
    {
        $balitaCari = Balita::factory()->create(['name' => 'Andi Unik']);
        Imunisasi::factory()->create(['balita_id' => $balitaCari->id]);
        Imunisasi::factory()->create(['balita_id' => $this->balita->id]);

        $response = $this->actingAs($this->nakes)->get(route('imunisasi.index', ['search' => 'Andi']));

        $response->assertOk();
        $response->assertSee('Andi Unik');
        $response->assertDontSee($this->balita->name);
    }

    /** @test */
    public function filter_jenis_vaksin_bekerja()
    {
        Imunisasi::factory()->create([
            'balita_id' => $this->balita->id,
            'jenis_vaksin' => 'BCG',
        ]);
        Imunisasi::factory()->create([
            'balita_id' => $this->balita->id,
            'jenis_vaksin' => 'Polio 1',
        ]);

        $response = $this->actingAs($this->nakes)->get(route('imunisasi.index', ['jenis_vaksin' => 'BCG']));

        $response->assertOk();
        $response->assertSee('BCG');
        $response->assertDontSee('Polio 1');
    }

    /** @test */
    public function filter_rentang_tanggal_bekerja()
    {
        Imunisasi::factory()->create([
            'balita_id' => $this->balita->id,
            'tanggal' => '2026-01-15',
        ]);
        Imunisasi::factory()->create([
            'balita_id' => $this->balita->id,
            'tanggal' => '2026-06-20',
        ]);

        $response = $this->actingAs($this->nakes)->get(route('imunisasi.index', [
            'tanggal_dari' => '2026-06-01',
            'tanggal_sampai' => '2026-06-30',
        ]));

        $response->assertOk();
        $response->assertSee('20 Jun 2026');
        $response->assertDontSee('15 Jan 2026');
    }

    /** @test */
    public function filter_balita_id_bekerja()
    {
        $balitaLain = Balita::factory()->create();
        Imunisasi::factory()->create(['balita_id' => $this->balita->id]);
        Imunisasi::factory()->create(['balita_id' => $balitaLain->id]);

        $response = $this->actingAs($this->nakes)->get(route('imunisasi.index', ['balita_id' => $this->balita->id]));

        $response->assertOk();
        $response->assertSee($this->balita->name);
        $response->assertDontSee($balitaLain->name);
    }

    /** @test */
    public function pengguna_tanpa_login_ditolak()
    {
        $response = $this->get(route('imunisasi.index'));
        
        // Jika autentikasi belum tersedia, test ini akan gagal
        // Sesuaikan dengan implementasi autentikasi Adhani
        $response->assertStatus(302);
    }
}
