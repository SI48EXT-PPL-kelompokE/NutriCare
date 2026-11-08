<?php

namespace Tests\Feature;

use App\Models\OrangTua;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class OrangTuaNikValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        // Gunakan database testing SQLite in-memory yang terisolasi sepenuhnya
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';

        parent::setUp();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'nik' => '3201015505920001',
            'name' => 'Siti Aminah',
            'alamat' => 'Jl. Melati No. 10, RT 01/RW 02',
            'kontak' => '081234567890',
            'pregnancy_status' => 'hamil',
            'create_account' => false,
        ], $overrides);
    }

    /**
     * Skenario 1: NIK valid 16 digit berhasil disimpan.
     */
    public function test_01_valid_16_digit_nik_is_stored_successfully(): void
    {
        $response = $this->postJson('/api/orang-tua', $this->validPayload());

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Data orang tua berhasil disimpan.',
                'data' => [
                    'nik' => '3201015505920001',
                    'name' => 'Siti Aminah',
                    'pregnancy_status' => 'hamil',
                    'has_account' => false,
                ],
            ]);

        $this->assertDatabaseHas('orang_tua', [
            'nik' => '3201015505920001',
            'name' => 'Siti Aminah',
        ]);
    }

    /**
     * Skenario 2: NIK kurang dari 16 digit ditolak.
     */
    public function test_02_nik_less_than_16_digits_is_rejected(): void
    {
        $response = $this->postJson('/api/orang-tua', $this->validPayload([
            'nik' => '320101550592000', // 15 digit
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik'])
            ->assertJsonPath('errors.nik.0', 'NIK harus tepat berjumlah 16 digit.');

        $this->assertDatabaseCount('orang_tua', 0);
    }

    /**
     * Skenario 3: NIK lebih dari 16 digit ditolak.
     */
    public function test_03_nik_more_than_16_digits_is_rejected(): void
    {
        $response = $this->postJson('/api/orang-tua', $this->validPayload([
            'nik' => '32010155059200012', // 17 digit
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik'])
            ->assertJsonPath('errors.nik.0', 'NIK harus tepat berjumlah 16 digit.');

        $this->assertDatabaseCount('orang_tua', 0);
    }

    /**
     * Skenario 4: NIK mengandung huruf ditolak.
     */
    public function test_04_nik_containing_letters_is_rejected(): void
    {
        $response = $this->postJson('/api/orang-tua', $this->validPayload([
            'nik' => '320101550592000A', // 16 karakter dengan huruf
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik'])
            ->assertJsonPath('errors.nik.0', 'NIK harus terdiri dari 16 digit angka (0-9).');

        $this->assertDatabaseCount('orang_tua', 0);
    }

    /**
     * Skenario 5: NIK mengandung simbol atau spasi ditolak.
     */
    public function test_05_nik_containing_symbols_or_spaces_is_rejected(): void
    {
        // Mengandung simbol
        $responseSymbol = $this->postJson('/api/orang-tua', $this->validPayload([
            'nik' => '320101550592000!',
        ]));

        $responseSymbol->assertStatus(422)
            ->assertJsonValidationErrors(['nik'])
            ->assertJsonPath('errors.nik.0', 'NIK harus terdiri dari 16 digit angka (0-9).');

        // Mengandung spasi di tengah
        $responseSpace = $this->postJson('/api/orang-tua', $this->validPayload([
            'nik' => '32010155 5920001',
        ]));

        $responseSpace->assertStatus(422)
            ->assertJsonValidationErrors(['nik'])
            ->assertJsonPath('errors.nik.0', 'NIK harus terdiri dari 16 digit angka (0-9).');

        $this->assertDatabaseCount('orang_tua', 0);
    }

    /**
     * Skenario 6: NIK kosong ditolak.
     */
    public function test_06_empty_nik_is_rejected(): void
    {
        $response = $this->postJson('/api/orang-tua', $this->validPayload([
            'nik' => '',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik'])
            ->assertJsonPath('errors.nik.0', 'NIK wajib diisi.');

        $this->assertDatabaseCount('orang_tua', 0);
    }

    /**
     * Skenario 7: NIK berupa array atau JSON number ditolak dengan HTTP 422 tanpa HTTP 500.
     */
    public function test_07_nik_as_array_or_json_number_is_rejected_without_http_500(): void
    {
        // Input berupa array
        $responseArray = $this->postJson('/api/orang-tua', $this->validPayload([
            'nik' => ['3201015505920001'],
        ]));

        $responseArray->assertStatus(422)
            ->assertJsonValidationErrors(['nik'])
            ->assertJsonPath('errors.nik.0', 'NIK harus berupa teks string.');

        // Input berupa JSON number (integer)
        $responseNumber = $this->postJson('/api/orang-tua', $this->validPayload([
            'nik' => 3201015505920001,
        ]));

        $responseNumber->assertStatus(422)
            ->assertJsonValidationErrors(['nik'])
            ->assertJsonPath('errors.nik.0', 'NIK harus berupa teks string.');

        $this->assertDatabaseCount('orang_tua', 0);
    }

    /**
     * Skenario 8: NIK dengan angka nol di depan tetap tersimpan utuh.
     */
    public function test_08_nik_with_leading_zero_is_preserved_intact(): void
    {
        $nikWithLeadingZero = '0102035505920001';

        $response = $this->postJson('/api/orang-tua', $this->validPayload([
            'nik' => $nikWithLeadingZero,
        ]));

        $response->assertStatus(201)
            ->assertJsonPath('data.nik', $nikWithLeadingZero);

        $this->assertDatabaseHas('orang_tua', [
            'nik' => $nikWithLeadingZero,
        ]);
    }

    /**
     * Skenario 9: CREATE dengan NIK duplikat ditolak.
     */
    public function test_09_create_with_duplicate_nik_is_rejected(): void
    {
        OrangTua::create([
            'user_id' => null,
            'nik' => '3201015505920001',
            'name' => 'Warga Pertama',
            'alamat' => 'Jl. Mawar No. 1',
            'kontak' => '081111111111',
            'pregnancy_status' => 'tidak_hamil',
        ]);

        $response = $this->postJson('/api/orang-tua', $this->validPayload([
            'nik' => '3201015505920001',
            'name' => 'Warga Kedua Duplikat',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik'])
            ->assertJsonPath('errors.nik.0', 'NIK sudah terdaftar dalam sistem.');

        $this->assertDatabaseCount('orang_tua', 1);
    }

    /**
     * Skenario 10: UPDATE menggunakan NIK milik record lain ditolak.
     */
    public function test_10_update_using_another_records_nik_is_rejected(): void
    {
        $orangTuaA = OrangTua::create([
            'user_id' => null,
            'nik' => '3201015505920001',
            'name' => 'Orang Tua A',
            'alamat' => 'Alamat A',
            'kontak' => '081111111111',
            'pregnancy_status' => 'tidak_hamil',
        ]);

        $orangTuaB = OrangTua::create([
            'user_id' => null,
            'nik' => '3201015505920002',
            'name' => 'Orang Tua B',
            'alamat' => 'Alamat B',
            'kontak' => '082222222222',
            'pregnancy_status' => 'hamil',
        ]);

        $response = $this->putJson("/api/orang-tua/{$orangTuaB->id}", [
            'nik' => $orangTuaA->nik, // Mencoba memakai NIK milik A
            'name' => 'Orang Tua B Update',
            'alamat' => 'Alamat B',
            'kontak' => '082222222222',
            'pregnancy_status' => 'hamil',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik'])
            ->assertJsonPath('errors.nik.0', 'NIK sudah terdaftar dalam sistem.');

        $this->assertDatabaseHas('orang_tua', [
            'id' => $orangTuaB->id,
            'nik' => '3201015505920002',
        ]);
    }

    /**
     * Skenario 11: UPDATE menggunakan NIK milik sendiri berhasil.
     */
    public function test_11_update_using_own_nik_succeeds(): void
    {
        $orangTua = OrangTua::create([
            'user_id' => null,
            'nik' => '3201015505920001',
            'name' => 'Nama Sebelum Update',
            'alamat' => 'Alamat Lama',
            'kontak' => '081111111111',
            'pregnancy_status' => 'tidak_hamil',
        ]);

        $response = $this->putJson("/api/orang-tua/{$orangTua->id}", [
            'nik' => '3201015505920001', // NIK milik sendiri
            'name' => 'Nama Setelah Update',
            'alamat' => 'Alamat Baru',
            'kontak' => '081999999999',
            'pregnancy_status' => 'hamil',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nik', '3201015505920001')
            ->assertJsonPath('data.name', 'Nama Setelah Update')
            ->assertJsonPath('data.pregnancy_status', 'hamil');

        $this->assertDatabaseHas('orang_tua', [
            'id' => $orangTua->id,
            'nik' => '3201015505920001',
            'name' => 'Nama Setelah Update',
        ]);
    }

    /**
     * Skenario 12: Validasi NIK gagal ketika create_account = true tidak menghasilkan akun User baru.
     */
    public function test_12_failed_nik_validation_with_create_account_does_not_create_user(): void
    {
        $response = $this->postJson('/api/orang-tua', [
            'nik' => '12345', // NIK tidak valid
            'name' => 'Calon Warga',
            'alamat' => 'Jl. Kenanga No. 5',
            'kontak' => '081234567890',
            'pregnancy_status' => 'tidak_hamil',
            'create_account' => true,
            'username' => 'calonwarga',
            'email' => 'calonwarga@nutricare.test',
            'password' => 'Password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik']);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('orang_tua', 0);
    }

    /**
     * Skenario 13: Perlindungan unique constraint database pada NIK & penanganan spesifik UniqueConstraintViolationException.
     */
    public function test_13_database_unique_constraint_and_specific_exception_handling(): void
    {
        OrangTua::create([
            'user_id' => null,
            'nik' => '3201015505920099',
            'name' => 'Warga Asli',
            'alamat' => 'Jl. Asli No. 1',
            'kontak' => '081234567890',
            'pregnancy_status' => 'tidak_hamil',
        ]);

        // Simulasikan race condition di mana insert ke-2 menabrak unique constraint DB secara langsung di rute API
        Route::post('/api/test-concurrent-nik', function () {
            OrangTua::create([
                'user_id' => null,
                'nik' => '3201015505920099', // Duplikat langsung ke DB melewati FormRequest
                'name' => 'Warga Konkuren',
                'alamat' => 'Jl. Konkuren No. 2',
                'kontak' => '081299999999',
                'pregnancy_status' => 'hamil',
            ]);
        });

        $responseNikConflict = $this->postJson('/api/test-concurrent-nik');

        $responseNikConflict->assertStatus(422)
            ->assertJsonValidationErrors(['nik'])
            ->assertJsonPath('errors.nik.0', 'NIK sudah terdaftar dalam sistem.');

        // Pastikan pelanggaran unique constraint tabel LAIN (mis. users.username) TIDAK disamarkan sebagai error NIK
        $role = Role::create(['name_role' => 'Warga / Orang Tua']);
        User::create([
            'role_id' => $role->id,
            'name' => 'User Satu',
            'username' => 'usersatu',
            'email' => 'usersatu@nutricare.test',
            'password' => 'password123',
            'active_status' => true,
        ]);

        Route::post('/api/test-other-unique-conflict', function () use ($role) {
            User::create([
                'role_id' => $role->id,
                'name' => 'User Dua',
                'username' => 'usersatu', // Duplikat username, bukan NIK
                'email' => 'userdua@nutricare.test',
                'password' => 'password123',
                'active_status' => true,
            ]);
        });

        $responseOtherConflict = $this->postJson('/api/test-other-unique-conflict');
        $responseOtherConflict->assertStatus(500);
    }

    /**
     * Skenario 14 (Regression KF-07): Seluruh operasi CRUD KF-07 dan pembuatan akun User opsional tetap berjalan normal.
     */
    public function test_14_kf07_regression_full_crud_and_optional_account(): void
    {
        // 1. CREATE dengan create_account = true
        $createResponse = $this->postJson('/api/orang-tua', [
            'nik' => '3201015505920077',
            'name' => 'Rina Marlina',
            'alamat' => 'Jl. Anggrek No. 12',
            'kontak' => '085612349876',
            'pregnancy_status' => 'tidak_hamil',
            'create_account' => true,
            'username' => 'rinamarlina',
            'email' => 'rina@nutricare.test',
            'password' => 'Password123',
        ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.has_account', true)
            ->assertJsonPath('data.user.username', 'rinamarlina');

        $orangTuaId = $createResponse->json('data.id');

        // 2. READ Index + Search
        $indexResponse = $this->getJson('/api/orang-tua?search=3201015505920077');
        $indexResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');

        // 3. READ Show
        $showResponse = $this->getJson("/api/orang-tua/{$orangTuaId}");
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.nik', '3201015505920077')
            ->assertJsonPath('data.user.email', 'rina@nutricare.test');

        // 4. UPDATE
        $updateResponse = $this->putJson("/api/orang-tua/{$orangTuaId}", [
            'nik' => '3201015505920077',
            'name' => 'Rina Marlina Updated',
            'alamat' => 'Jl. Anggrek No. 15',
            'kontak' => '085612349876',
            'pregnancy_status' => 'hamil',
        ]);
        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'Rina Marlina Updated')
            ->assertJsonPath('data.user.name', 'Rina Marlina Updated');

        // 5. DELETE
        $deleteResponse = $this->deleteJson("/api/orang-tua/{$orangTuaId}");
        $deleteResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('orang_tua', 0);
        $this->assertDatabaseCount('users', 0);
    }
}

