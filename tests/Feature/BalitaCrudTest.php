<?php

namespace Tests\Feature;

use App\Http\Resources\BalitaResource;
use App\Models\Balita;
use App\Models\OrangTua;
use App\Models\PengukuranKms;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BalitaCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        if (! isset(RefreshDatabaseState::$inMemoryConnections['sqlite'])) {
            RefreshDatabaseState::$migrated = false;
        }
    }

    public function createApplication()
    {
        $app = parent::createApplication();
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.url', null);
        $app['config']->set('database.connections.sqlite.database', ':memory:');

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->userWithPermissions('Kader Posyandu', [
            'balita' => ['can_view', 'can_create', 'can_edit', 'can_delete'],
            'orang_tua' => ['can_view', 'can_create', 'can_edit', 'can_delete'],
        ]));
    }

    private function createOrangTua(array $overrides = []): OrangTua
    {
        return OrangTua::create(array_merge([
            'nik' => '3273012808240003',
            'name' => 'Siti Aminah',
            'alamat' => 'Jl. Mawar No. 10, RT 01 / RW 03',
            'kontak' => '081234567890',
            'pregnancy_status' => 'tidak_hamil',
        ], $overrides));
    }

    private function validBalitaPayload(int $orangTuaId, array $overrides = []): array
    {
        return array_merge([
            'orang_tua_id' => $orangTuaId,
            'name' => 'Ahmad Rayhan',
            'birth_date' => '2024-05-15',
            'jenis_kelamin' => 'L',
        ], $overrides);
    }

    /**
     * Skenario 1: CREATE balita dengan data valid berhasil (201 Created).
     */
    public function test_01_create_balita_with_valid_data_succeeds(): void
    {
        $orangTua = $this->createOrangTua();

        $response = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id));

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Data balita berhasil ditambahkan.',
                'data' => [
                    'orang_tua_id' => $orangTua->id,
                    'name' => 'Ahmad Rayhan',
                    'birth_date' => '2024-05-15',
                    'jenis_kelamin' => 'L',
                    'orang_tua' => [
                        'id' => $orangTua->id,
                        'nik' => '3273012808240003',
                        'name' => 'Siti Aminah',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('balita', [
            'orang_tua_id' => $orangTua->id,
            'name' => 'Ahmad Rayhan',
            'jenis_kelamin' => 'L',
        ]);
    }

    /**
     * Skenario 2: CREATE dengan nama kosong ditolak (422).
     */
    public function test_02_create_balita_with_empty_name_is_rejected(): void
    {
        $orangTua = $this->createOrangTua();

        $response = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id, [
            'name' => '',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonPath('errors.name.0', 'Nama lengkap balita wajib diisi.');

        $this->assertDatabaseCount('balita', 0);
    }

    /**
     * Skenario 3: CREATE dengan tanggal lahir tidak valid ditolak (422).
     */
    public function test_03_create_balita_with_invalid_birth_date_is_rejected(): void
    {
        $orangTua = $this->createOrangTua();

        $response = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id, [
            'birth_date' => 'bukan-tanggal',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['birth_date']);

        $this->assertDatabaseCount('balita', 0);
    }

    /**
     * Skenario 4: CREATE dengan tanggal lahir di masa depan ditolak (422).
     */
    public function test_04_create_balita_with_future_birth_date_is_rejected(): void
    {
        $orangTua = $this->createOrangTua();
        $futureDate = now()->addDays(7)->format('Y-m-d');

        $response = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id, [
            'birth_date' => $futureDate,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['birth_date'])
            ->assertJsonPath('errors.birth_date.0', 'Tanggal lahir tidak boleh berada di masa depan.');

        $this->assertDatabaseCount('balita', 0);
    }

    /**
     * Skenario 5: CREATE dengan jenis kelamin selain L/P ditolak (422).
     */
    public function test_05_create_balita_with_invalid_jenis_kelamin_is_rejected(): void
    {
        $orangTua = $this->createOrangTua();

        $response = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id, [
            'jenis_kelamin' => 'X',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['jenis_kelamin'])
            ->assertJsonPath('errors.jenis_kelamin.0', 'Jenis kelamin hanya boleh bernilai "L" (Laki-laki) atau "P" (Perempuan).');

        $this->assertDatabaseCount('balita', 0);
    }

    /**
     * Skenario 6: CREATE dengan orang_tua_id yang tidak ada ditolak (422).
     */
    public function test_06_create_balita_with_non_existent_orang_tua_id_is_rejected(): void
    {
        $response = $this->postJson('/api/balita', $this->validBalitaPayload(999999));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orang_tua_id'])
            ->assertJsonPath('errors.orang_tua_id.0', 'Data orang tua yang dipilih tidak terdaftar dalam sistem.');

        $this->assertDatabaseCount('balita', 0);
    }

    /**
     * Skenario 7: READ daftar balita berhasil, mendukung pencarian dan filter.
     */
    public function test_07_read_balita_list_with_pagination_search_and_filter(): void
    {
        $orangTua1 = $this->createOrangTua([
            'nik' => '3273012808240001',
            'name' => 'Siti Aminah',
        ]);
        $orangTua2 = $this->createOrangTua([
            'nik' => '3273012808240002',
            'name' => 'Dewi Lestari',
        ]);

        $this->postJson('/api/balita', $this->validBalitaPayload($orangTua1->id, [
            'name' => 'Ahmad Rayhan',
            'jenis_kelamin' => 'L',
        ]));
        $this->postJson('/api/balita', $this->validBalitaPayload($orangTua2->id, [
            'name' => 'Nabila Putri',
            'jenis_kelamin' => 'P',
        ]));

        // Semua data
        $responseAll = $this->getJson('/api/balita');
        $responseAll->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        // Filter search berdasarkan nama balita
        $responseSearch = $this->getJson('/api/balita?search=Nabila');
        $responseSearch->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Nabila Putri');

        // Filter berdasarkan jenis_kelamin
        $responseGender = $this->getJson('/api/balita?jenis_kelamin=L');
        $responseGender->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Ahmad Rayhan');

        // Filter berdasarkan orang_tua_id
        $responseParent = $this->getJson('/api/balita?orang_tua_id='.$orangTua2->id);
        $responseParent->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Nabila Putri');
    }

    /**
     * Skenario 8: READ detail balita berhasil dan mengembalikan 404 untuk ID tidak ditemukan.
     */
    public function test_08_read_balita_detail_succeeds_and_returns_404_when_not_found(): void
    {
        $orangTua = $this->createOrangTua();
        $storeResponse = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id));
        $balitaId = $storeResponse->json('data.id');

        $response = $this->getJson("/api/balita/{$balitaId}");
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Detail data balita berhasil diambil.',
                'data' => [
                    'id' => $balitaId,
                    'orang_tua_id' => $orangTua->id,
                    'name' => 'Ahmad Rayhan',
                    'birth_date' => '2024-05-15',
                    'jenis_kelamin' => 'L',
                ],
            ]);

        $notFoundResponse = $this->getJson('/api/balita/999999');
        $notFoundResponse->assertStatus(404);
    }

    /**
     * Skenario 9: UPDATE data balita (PUT) berhasil.
     */
    public function test_09_update_balita_via_put_succeeds(): void
    {
        $orangTua = $this->createOrangTua();
        $storeResponse = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id));
        $balitaId = $storeResponse->json('data.id');

        $updateResponse = $this->putJson("/api/balita/{$balitaId}", [
            'orang_tua_id' => $orangTua->id,
            'name' => 'Ahmad Rayhan Pratama',
            'birth_date' => '2024-04-10',
            'jenis_kelamin' => 'L',
        ]);

        $updateResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data balita berhasil diperbarui.',
                'data' => [
                    'id' => $balitaId,
                    'name' => 'Ahmad Rayhan Pratama',
                    'birth_date' => '2024-04-10',
                    'jenis_kelamin' => 'L',
                ],
            ]);

        $this->assertDatabaseHas('balita', [
            'id' => $balitaId,
            'name' => 'Ahmad Rayhan Pratama',
        ]);
    }

    /**
     * Skenario 10: UPDATE relasi orang_tua_id ke orang tua lain berhasil.
     */
    public function test_10_update_balita_orang_tua_relation_succeeds(): void
    {
        $orangTua1 = $this->createOrangTua(['nik' => '3273012808240001', 'name' => 'Siti Aminah']);
        $orangTua2 = $this->createOrangTua(['nik' => '3273012808240002', 'name' => 'Dewi Lestari']);

        $storeResponse = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua1->id));
        $balitaId = $storeResponse->json('data.id');

        $updateResponse = $this->putJson("/api/balita/{$balitaId}", [
            'orang_tua_id' => $orangTua2->id,
            'name' => 'Ahmad Rayhan',
            'birth_date' => '2024-05-15',
            'jenis_kelamin' => 'L',
        ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.orang_tua_id', $orangTua2->id)
            ->assertJsonPath('data.orang_tua.name', 'Dewi Lestari');

        $this->assertDatabaseHas('balita', [
            'id' => $balitaId,
            'orang_tua_id' => $orangTua2->id,
        ]);
    }

    /**
     * Skenario 11: Partial UPDATE menggunakan PATCH /api/balita/{balita} berhasil mengubah sebagian field.
     */
    public function test_11_partial_update_balita_via_patch_succeeds(): void
    {
        $orangTua = $this->createOrangTua();
        $storeResponse = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id, [
            'name' => 'Nabila Putri',
            'birth_date' => '2024-06-01',
            'jenis_kelamin' => 'P',
        ]));
        $balitaId = $storeResponse->json('data.id');

        // Hanya mengirim field name via PATCH
        $patchResponse = $this->patchJson("/api/balita/{$balitaId}", [
            'name' => 'Nabila Putri Azzahra',
        ]);

        $patchResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data balita berhasil diperbarui.',
                'data' => [
                    'id' => $balitaId,
                    'orang_tua_id' => $orangTua->id,
                    'name' => 'Nabila Putri Azzahra',
                    'birth_date' => '2024-06-01',
                    'jenis_kelamin' => 'P',
                ],
            ]);
    }

    /**
     * Skenario 12: DELETE balita tanpa dependensi berhasil (200 OK).
     */
    public function test_12_delete_balita_without_dependencies_succeeds(): void
    {
        $orangTua = $this->createOrangTua();
        $storeResponse = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id));
        $balitaId = $storeResponse->json('data.id');

        $deleteResponse = $this->deleteJson("/api/balita/{$balitaId}");

        $deleteResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data balita berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('balita', ['id' => $balitaId]);
    }

    /**
     * Skenario 13: DELETE balita yang memiliki dependensi ditolak secara terkendali (422).
     */
    public function test_13_delete_balita_with_service_dependencies_is_rejected_gracefully(): void
    {
        $orangTua = $this->createOrangTua();
        $storeResponse = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id));
        $balitaId = $storeResponse->json('data.id');

        $role = Role::create(['name_role' => 'kader']);
        $petugas = User::create([
            'role_id' => $role->id,
            'name' => 'Petugas Kader',
            'username' => 'kader_test',
            'email' => 'kader@nutricare.test',
            'password' => bcrypt('password'),
            'active_status' => true,
        ]);

        PengukuranKms::create([
            'balita_id' => $balitaId,
            'user_id' => $petugas->id,
            'date' => '2025-01-10',
            'berat_badan' => 9.50,
            'tinggi_badan' => 75.0,
            'status_gizi' => 'Normal',
        ]);

        $deleteResponse = $this->deleteJson("/api/balita/{$balitaId}");

        $deleteResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Data balita tidak dapat dihapus karena masih digunakan pada riwayat layanan kesehatan atau posyandu.',
            ]);

        $this->assertDatabaseHas('balita', ['id' => $balitaId]);
    }

    /**
     * Skenario 14: Format respons JSON sesuai BalitaResource.
     */
    public function test_14_json_response_structure_matches_balita_resource(): void
    {
        $orangTua = $this->createOrangTua();
        $response = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id));

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'orang_tua_id',
                    'name',
                    'birth_date',
                    'jenis_kelamin',
                    'qr_code',
                    'orang_tua' => [
                        'id',
                        'nik',
                        'name',
                        'alamat',
                        'kontak',
                        'pregnancy_status',
                    ],
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    /**
     * Skenario 15: qr_code otomatis dihasilkan dengan format NC-XXXXXXXXXX, unik, dan mengabaikan input client.
     */
    public function test_15_qr_code_is_auto_generated_unique_and_ignores_client_input(): void
    {
        $orangTua = $this->createOrangTua();

        $res1 = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id, [
            'name' => 'Balita Satu',
            'qr_code' => 'CUSTOM-HACK-001',
        ]));
        $res2 = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id, [
            'name' => 'Balita Dua',
        ]));

        $qr1 = $res1->json('data.qr_code');
        $qr2 = $res2->json('data.qr_code');

        $this->assertNotEquals('CUSTOM-HACK-001', $qr1);
        $this->assertMatchesRegularExpression('/^NC-[A-Z0-9]{10}$/', $qr1);
        $this->assertMatchesRegularExpression('/^NC-[A-Z0-9]{10}$/', $qr2);
        $this->assertNotEquals($qr1, $qr2);
    }

    /**
     * Skenario 16: qr_code tidak berubah saat data balita di-update (PUT maupun PATCH).
     */
    public function test_16_qr_code_remains_unchanged_when_balita_is_updated(): void
    {
        $orangTua = $this->createOrangTua();
        $storeResponse = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id));
        $balitaId = $storeResponse->json('data.id');
        $originalQrCode = $storeResponse->json('data.qr_code');

        $putResponse = $this->putJson("/api/balita/{$balitaId}", [
            'orang_tua_id' => $orangTua->id,
            'name' => 'Ahmad Rayhan Updated',
            'birth_date' => '2024-05-15',
            'jenis_kelamin' => 'L',
            'qr_code' => 'NC-OVERRIDE99',
        ]);

        $putResponse->assertStatus(200)
            ->assertJsonPath('data.qr_code', $originalQrCode);

        $patchResponse = $this->patchJson("/api/balita/{$balitaId}", [
            'name' => 'Ahmad Rayhan Patched',
            'qr_code' => 'NC-OVERRIDE88',
        ]);

        $patchResponse->assertStatus(200)
            ->assertJsonPath('data.qr_code', $originalQrCode);
    }

    /**
     * Skenario 17: Relasi OrangTua dan Balita berjalan dengan benar (termasuk proteksi hapus OrangTua).
     */
    public function test_17_orang_tua_and_balita_relationship_works_correctly(): void
    {
        $orangTua = $this->createOrangTua();
        $storeResponse = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id));
        $balitaId = $storeResponse->json('data.id');

        $balita = Balita::with('orangTua')->findOrFail($balitaId);
        $this->assertInstanceOf(OrangTua::class, $balita->orangTua);
        $this->assertEquals($orangTua->id, $balita->orangTua->id);
        $this->assertEquals(1, $orangTua->balita()->count());

        // Orang tua yang masih memiliki balita tidak boleh dihapus melalui API OrangTua (KF-07)
        $deleteParentResponse = $this->deleteJson("/api/orang-tua/{$orangTua->id}");
        $deleteParentResponse->assertStatus(422);
    }

    /**
     * Skenario 18: Halaman UI daftar balita dan detail balita dapat diakses dengan baik.
     */
    public function test_18_frontend_balita_pages_render_properly(): void
    {
        $orangTua = $this->createOrangTua([
            'nik' => '3273012808240003',
            'name' => 'Siti Aminah',
        ]);
        $storeResponse = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id, [
            'name' => 'Ahmad Rayhan',
        ]));
        $balitaId = $storeResponse->json('data.id');

        // Halaman Daftar Balita
        $indexResponse = $this->get('/kependudukan/balita');
        $indexResponse->assertStatus(200)
            ->assertSee('Data Balita & Kependudukan', false)
            ->assertSee('href="'.route('kependudukan.orang-tua.index').'"', false)
            ->assertSee('Tambah Balita Baru')
            ->assertSee('Ahmad Rayhan')
            ->assertSee('Siti Aminah')
            ->assertSee('3273012808240003');

        // Halaman Detail Balita
        $showResponse = $this->get("/kependudukan/balita/{$balitaId}");
        $showResponse->assertStatus(200)
            ->assertSee('Detail Data Balita')
            ->assertSee('Ahmad Rayhan')
            ->assertSee('Siti Aminah')
            ->assertSee('Kembali');

        $this->get(route('kependudukan.orang-tua.index'))
            ->assertOk()
            ->assertSee('href="'.route('kependudukan.balita.index').'"', false)
            ->assertSee('Siti Aminah');
    }

    public function test_balita_viewer_without_orang_tua_permission_does_not_see_the_orang_tua_tab(): void
    {
        $user = $this->userWithPermissions('Admin / Kepala Puskesmas', [
            'balita' => ['can_view'],
        ]);

        $this->actingAs($user)->get(route('kependudukan.balita.index'))
            ->assertOk()
            ->assertDontSee('href="'.route('kependudukan.orang-tua.index').'"', false);

        $this->get(route('kependudukan.orang-tua.index'))->assertForbidden();
    }

    /**
     * Skenario 19 (KF-11): Balita usia 0 bulan dihitung dengan benar pada clock dibekukan.
     */
    public function test_19_kf11_age_calculation_zero_months(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 20, 10, 0, 0, config('app.timezone')));

        $orangTua = $this->createOrangTua();

        // Lahir di hari yang sama (2026-05-20) -> 0 Bulan
        $resSameDay = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id, [
            'name' => 'Bayi Baru Lahir',
            'birth_date' => '2026-05-20',
        ]));
        $resSameDay->assertStatus(201)
            ->assertJsonPath('data.usia_bulan', 0)
            ->assertJsonPath('data.usia_label', '0 Bulan');

        // Lahir 19 hari lalu (2026-05-01) -> 0 Bulan
        $res19Days = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id, [
            'name' => 'Bayi 19 Hari',
            'birth_date' => '2026-05-01',
        ]));
        $res19Days->assertStatus(201)
            ->assertJsonPath('data.usia_bulan', 0)
            ->assertJsonPath('data.usia_label', '0 Bulan');

        // Antisipasi tanggal masa depan pada helper -> tidak negatif (0)
        $this->assertSame(0, BalitaResource::calculateUsiaBulan('2026-06-01'));

        Carbon::setTestNow();
    }

    /**
     * Skenario 20 (KF-11): Balita usia 1 bulan penuh dihitung dengan benar.
     */
    public function test_20_kf11_age_calculation_one_full_month(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 20, 10, 0, 0, config('app.timezone')));

        $orangTua = $this->createOrangTua();

        // Tepat 1 bulan (lahir 2026-04-20, hari ini 2026-05-20) -> 1 Bulan
        $resExact = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id, [
            'name' => 'Balita Tepat 1 Bulan',
            'birth_date' => '2026-04-20',
        ]));
        $resExact->assertStatus(201)
            ->assertJsonPath('data.usia_bulan', 1)
            ->assertJsonPath('data.usia_label', '1 Bulan');

        // 1 bulan lewat 5 hari (lahir 2026-04-15, hari ini 2026-05-20) -> 1 Bulan
        $resPlusDays = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id, [
            'name' => 'Balita 1 Bulan 5 Hari',
            'birth_date' => '2026-04-15',
        ]));
        $resPlusDays->assertStatus(201)
            ->assertJsonPath('data.usia_bulan', 1)
            ->assertJsonPath('data.usia_label', '1 Bulan');

        Carbon::setTestNow();
    }

    /**
     * Skenario 21 (KF-11): Balita berusia beberapa tahun (misal 26 Bulan & 40 Bulan).
     */
    public function test_21_kf11_age_calculation_multiple_years(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 20, 10, 0, 0, config('app.timezone')));

        $orangTua = $this->createOrangTua();

        // Lahir 2024-03-20 -> 2 tahun 2 bulan = 26 Bulan
        $res26 = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id, [
            'name' => 'Ahmad Rayhan',
            'birth_date' => '2024-03-20',
        ]));
        $res26->assertStatus(201)
            ->assertJsonPath('data.usia_bulan', 26)
            ->assertJsonPath('data.usia_label', '26 Bulan');

        // Lahir 2023-01-10 -> 3 tahun 4 bulan = 40 Bulan
        $res40 = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id, [
            'name' => 'Zayan Ghani',
            'birth_date' => '2023-01-10',
        ]));
        $res40->assertStatus(201)
            ->assertJsonPath('data.usia_bulan', 40)
            ->assertJsonPath('data.usia_label', '40 Bulan');

        Carbon::setTestNow();
    }

    /**
     * Skenario 22 (KF-11): Perhitungan saat tanggal ulang bulan pada bulan berjalan belum tercapai.
     */
    public function test_22_kf11_age_calculation_when_monthly_anniversary_not_yet_reached(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 19, 10, 0, 0, config('app.timezone')));

        // Lahir 2026-04-20, hari ini 2026-05-19 (kurang 1 hari menuju 1 bulan) -> 0 Bulan
        $this->assertSame(0, BalitaResource::calculateUsiaBulan('2026-04-20'));
        $this->assertSame('0 Bulan', BalitaResource::formatUsia('2026-04-20'));

        // Lahir 2025-05-20, hari ini 2026-05-19 (kurang 1 hari menuju 12 bulan) -> 11 Bulan
        $this->assertSame(11, BalitaResource::calculateUsiaBulan('2025-05-20'));
        $this->assertSame('11 Bulan', BalitaResource::formatUsia('2025-05-20'));

        Carbon::setTestNow();
    }

    /**
     * Skenario 23 (KF-11): Pergantian tahun dan kasus tanggal akhir bulan (31 Jan -> Feb -> Mar).
     */
    public function test_23_kf11_age_calculation_year_rollover_and_end_of_month(): void
    {
        // 1. Pergantian tahun: lahir 2025-11-15
        Carbon::setTestNow(Carbon::create(2026, 2, 14, 10, 0, 0, config('app.timezone')));
        $this->assertSame(2, BalitaResource::calculateUsiaBulan('2025-11-15'));

        Carbon::setTestNow(Carbon::create(2026, 2, 15, 10, 0, 0, config('app.timezone')));
        $this->assertSame(3, BalitaResource::calculateUsiaBulan('2025-11-15'));

        // 2. Kasus akhir bulan: lahir 2026-01-31
        // Pada 2026-02-27 (belum akhir bulan Februari) -> 0 Bulan
        Carbon::setTestNow(Carbon::create(2026, 2, 27, 10, 0, 0, config('app.timezone')));
        $this->assertSame(0, BalitaResource::calculateUsiaBulan('2026-01-31'));

        // Pada 2026-02-28 (akhir bulan Februari tahun non-kabisat) -> 1 Bulan penuh
        Carbon::setTestNow(Carbon::create(2026, 2, 28, 10, 0, 0, config('app.timezone')));
        $this->assertSame(1, BalitaResource::calculateUsiaBulan('2026-01-31'));

        // Pada 2026-03-30 (belum tanggal 31 Maret) -> masih 1 Bulan
        Carbon::setTestNow(Carbon::create(2026, 3, 30, 10, 0, 0, config('app.timezone')));
        $this->assertSame(1, BalitaResource::calculateUsiaBulan('2026-01-31'));

        // Pada 2026-03-31 -> genap 2 Bulan
        Carbon::setTestNow(Carbon::create(2026, 3, 31, 10, 0, 0, config('app.timezone')));
        $this->assertSame(2, BalitaResource::calculateUsiaBulan('2026-01-31'));

        Carbon::setTestNow();
    }

    /**
     * Skenario 24 (KF-11): Perhitungan pada tanggal tahun kabisat (29 Februari).
     */
    public function test_24_kf11_age_calculation_on_leap_year_date(): void
    {
        // Balita lahir pada tahun kabisat 2024-02-29
        // Pada 2024-03-28 -> 0 Bulan
        Carbon::setTestNow(Carbon::create(2024, 3, 28, 10, 0, 0, config('app.timezone')));
        $this->assertSame(0, BalitaResource::calculateUsiaBulan('2024-02-29'));

        // Pada 2024-03-29 -> 1 Bulan
        Carbon::setTestNow(Carbon::create(2024, 3, 29, 10, 0, 0, config('app.timezone')));
        $this->assertSame(1, BalitaResource::calculateUsiaBulan('2024-02-29'));

        // Pada tahun non-kabisat berikutnya: 2025-02-27 -> 11 Bulan
        Carbon::setTestNow(Carbon::create(2025, 2, 27, 10, 0, 0, config('app.timezone')));
        $this->assertSame(11, BalitaResource::calculateUsiaBulan('2024-02-29'));

        // Pada akhir Februari tahun non-kabisat: 2025-02-28 -> genap 12 Bulan
        Carbon::setTestNow(Carbon::create(2025, 2, 28, 10, 0, 0, config('app.timezone')));
        $this->assertSame(12, BalitaResource::calculateUsiaBulan('2024-02-29'));

        Carbon::setTestNow();
    }

    /**
     * Skenario 25 (KF-11): Perubahan usia otomatis setelah birth_date diedit.
     */
    public function test_25_kf11_age_automatically_updates_after_birth_date_is_edited(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 20, 10, 0, 0, config('app.timezone')));

        $orangTua = $this->createOrangTua();

        // Awalnya lahir 2025-05-20 (12 Bulan)
        $storeRes = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id, [
            'name' => 'Nabila Putri',
            'birth_date' => '2025-05-20',
            'jenis_kelamin' => 'P',
        ]));
        $balitaId = $storeRes->json('data.id');
        $storeRes->assertJsonPath('data.usia_bulan', 12)
            ->assertJsonPath('data.usia_label', '12 Bulan');

        // Diperbarui menjadi 2024-11-20 (18 Bulan)
        $updateRes = $this->patchJson("/api/balita/{$balitaId}", [
            'birth_date' => '2024-11-20',
        ]);
        $updateRes->assertStatus(200)
            ->assertJsonPath('data.birth_date', '2024-11-20')
            ->assertJsonPath('data.usia_bulan', 18)
            ->assertJsonPath('data.usia_label', '18 Bulan');

        // Pastikan di halaman tabel UI juga berubah menjadi 18 Bulan
        $indexRes = $this->get('/kependudukan/balita');
        $indexRes->assertStatus(200)
            ->assertSee('18 Bulan');

        Carbon::setTestNow();
    }

    /**
     * Skenario 26 (KF-11 & UI): Tabel menampilkan kolom USIA ("X Bulan"), tombol QR ringkas, Lihat KMS disabled, dan dropdown aksi.
     */
    public function test_26_kf11_table_displays_usia_format_qr_button_and_action_dropdown(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 20, 10, 0, 0, config('app.timezone')));

        $orangTua = $this->createOrangTua([
            'nik' => '3273012808240003',
            'name' => 'Siti Aminah',
        ]);
        $storeRes = $this->postJson('/api/balita', $this->validBalitaPayload($orangTua->id, [
            'name' => 'Ahmad Rayhan',
            'birth_date' => '2024-03-20', // 26 Bulan
        ]));
        $balitaId = $storeRes->json('data.id');

        $response = $this->get('/kependudukan/balita');

        $response->assertStatus(200)
            ->assertSee('Usia')
            ->assertSee('26 Bulan')
            ->assertSee('QR Identitas Ahmad Rayhan')
            ->assertSee('Lihat KMS')
            ->assertSee('Integrasi KMS belum tersedia')
            ->assertSee('Lihat Detail')
            ->assertSee('Edit Data Balita')
            ->assertSee('Hapus Data Balita')
            ->assertSee("action-menu-{$balitaId}", false);

        Carbon::setTestNow();
    }

    /**
     * Skenario 27 (KF-11): Tabel database balita tidak memiliki kolom statis usia baru.
     */
    public function test_27_kf11_database_does_not_have_static_age_column(): void
    {
        $this->assertTrue(Schema::hasColumn('balita', 'birth_date'));
        $this->assertFalse(Schema::hasColumn('balita', 'usia'));
        $this->assertFalse(Schema::hasColumn('balita', 'usia_bulan'));
    }
}
