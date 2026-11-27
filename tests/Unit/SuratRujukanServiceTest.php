<?php

namespace Tests\Unit;

use App\Models\Balita;
use App\Models\PengukuranKms;
use App\Models\Role;
use App\Models\SuratRujukan;
use App\Models\User;
use App\Services\SuratRujukanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratRujukanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SuratRujukanService $service;
    protected User $nakes;
    protected Balita $balita;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new SuratRujukanService();

        $nakesRole = Role::firstOrCreate(['name_role' => 'Nakes / Bidan'], ['description' => 'Nakes']);
        $this->nakes = User::factory()->create([
            'role_id' => $nakesRole->id,
            'name' => 'Bidan Siti, S.Tr.Keb',
        ]);

        $this->balita = Balita::factory()->create(['name' => 'Ahmad Balita']);

        // Prasyarat KMS: sediakan hasil pengukuran KMS untuk balita
        PengukuranKms::create([
            'balita_id' => $this->balita->id,
            'user_id' => $this->nakes->id,
            'date' => now()->toDateString(),
            'berat_badan' => 7.5,
            'tinggi_badan' => 70.0,
            'status_gizi' => 'Gizi Kurang',
        ]);
    }

    public function test_generate_nomor_surat_format_and_sequence(): void
    {
        $nomor1 = $this->service->generateNomorSurat();
        $expectedPrefix = 'SR-' . now()->format('Ymd') . '-';
        $this->assertStringStartsWith($expectedPrefix, $nomor1);
        $this->assertEquals($expectedPrefix . '0001', $nomor1);

        // Simpan surat dengan nomor tersebut
        SuratRujukan::factory()->create(['nomor_surat' => $nomor1]);

        // Generate berikutnya harus 0002
        $nomor2 = $this->service->generateNomorSurat();
        $this->assertEquals($expectedPrefix . '0002', $nomor2);
    }

    public function test_create_surat_rujukan_successfully(): void
    {
        $data = [
            'balita_id' => $this->balita->id,
            'faskes_tujuan' => 'RSUD dr. Soetomo Surabaya',
            'alasan' => 'Gizi buruk klinis dan penurunan berat badan 3 bulan berturut-turut.',
            'status_follow_up' => 'belum',
        ];

        $surat = $this->service->createSurat($data, $this->nakes->id);

        $this->assertInstanceOf(SuratRujukan::class, $surat);
        $this->assertEquals($this->balita->id, $surat->balita_id);
        $this->assertEquals($this->nakes->id, $surat->user_id);
        $this->assertEquals('RSUD dr. Soetomo Surabaya', $surat->faskes_tujuan);
        $this->assertEquals('belum', $surat->status_follow_up);
        $this->assertNotEmpty($surat->nomor_surat);
        $this->assertNotEmpty($surat->qr_verification_code);

        $this->assertDatabaseHas('surat_rujukan', [
            'id' => $surat->id,
            'nomor_surat' => $surat->nomor_surat,
        ]);
    }

    public function test_update_surat_rujukan_does_not_change_nomor_or_qr(): void
    {
        $surat = $this->service->createSurat([
            'balita_id' => $this->balita->id,
            'faskes_tujuan' => 'Puskesmas Rujukan',
            'alasan' => 'Alasan awal',
            'status_follow_up' => 'belum',
        ], $this->nakes->id);

        $originalNomor = $surat->nomor_surat;
        $originalQr = $surat->qr_verification_code;

        $updated = $this->service->updateSurat($surat, [
            'faskes_tujuan' => 'RSUP Dr. Sardjito',
            'alasan' => 'Alasan diperbarui dengan hasil lab',
            'status_follow_up' => 'proses',
        ]);

        $this->assertEquals('RSUP Dr. Sardjito', $updated->faskes_tujuan);
        $this->assertEquals('Alasan diperbarui dengan hasil lab', $updated->alasan);
        $this->assertEquals('proses', $updated->status_follow_up);
        $this->assertEquals($originalNomor, $updated->nomor_surat);
        $this->assertEquals($originalQr, $updated->qr_verification_code);
    }

    public function test_delete_surat_rujukan(): void
    {
        $surat = SuratRujukan::factory()->create([
            'balita_id' => $this->balita->id,
            'user_id' => $this->nakes->id,
        ]);

        $result = $this->service->deleteSurat($surat);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('surat_rujukan', ['id' => $surat->id]);
    }

    public function test_verify_surat_rujukan_returns_valid_record(): void
    {
        $surat = SuratRujukan::factory()->create([
            'balita_id' => $this->balita->id,
            'user_id' => $this->nakes->id,
        ]);

        $found = $this->service->verifySurat($surat->qr_verification_code);
        $this->assertNotNull($found);
        $this->assertEquals($surat->id, $found->id);

        $notFound = $this->service->verifySurat('invalid-code-12345');
        $this->assertNull($notFound);
    }

    public function test_get_saran_rujukan_filters_at_risk_balita_without_active_referral(): void
    {
        // Balita 1: Gizi Buruk (belum dirujuk) -> harus masuk saran
        $balita1 = Balita::factory()->create(['name' => 'Balita Perlu Rujuk']);
        PengukuranKms::create([
            'balita_id' => $balita1->id,
            'user_id' => $this->nakes->id,
            'date' => now()->toDateString(),
            'berat_badan' => 7.2,
            'tinggi_badan' => 75.0,
            'status_gizi' => 'Gizi Buruk',
        ]);

        // Balita 2: Gizi Baik -> TIDAK masuk saran
        $balita2 = Balita::factory()->create(['name' => 'Balita Gizi Baik']);
        PengukuranKms::create([
            'balita_id' => $balita2->id,
            'user_id' => $this->nakes->id,
            'date' => now()->toDateString(),
            'berat_badan' => 11.5,
            'tinggi_badan' => 85.0,
            'status_gizi' => 'Gizi Baik',
        ]);

        // Balita 3: Gizi Buruk tapi SUDAH punya surat rujukan aktif (belum follow up) -> TIDAK masuk saran
        $balita3 = Balita::factory()->create(['name' => 'Balita Sudah Dirujuk']);
        PengukuranKms::create([
            'balita_id' => $balita3->id,
            'user_id' => $this->nakes->id,
            'date' => now()->toDateString(),
            'berat_badan' => 6.8,
            'tinggi_badan' => 73.0,
            'status_gizi' => 'Gizi Buruk',
        ]);
        SuratRujukan::factory()->create([
            'balita_id' => $balita3->id,
            'user_id' => $this->nakes->id,
            'status_follow_up' => 'belum',
        ]);

        $saran = $this->service->getSaranRujukan();

        $this->assertTrue($saran->contains('id', $balita1->id));
        $this->assertFalse($saran->contains('id', $balita2->id));
        $this->assertFalse($saran->contains('id', $balita3->id));
    }

    public function test_generate_nomor_surat_handles_numbers_above_9999(): void
    {
        $prefix = 'SR-' . now()->format('Ymd') . '-';
        SuratRujukan::factory()->create(['nomor_surat' => $prefix . '9999']);

        $nomor10000 = $this->service->generateNomorSurat();
        $this->assertEquals($prefix . '10000', $nomor10000);

        // Buat surat dengan 10000 dan alokasikan nomor berikutnya -> harus 10001
        SuratRujukan::factory()->create(['nomor_surat' => $nomor10000]);
        $nomor10001 = $this->service->generateNomorSurat();
        $this->assertEquals($prefix . '10001', $nomor10001);
    }

    public function test_deleted_last_surat_does_not_reuse_allocated_number(): void
    {
        $data = [
            'balita_id' => $this->balita->id,
            'faskes_tujuan' => 'RSUD dr. Soetomo',
            'alasan' => 'Pemeriksaan lanjutan gizi',
            'status_follow_up' => 'belum',
        ];

        // Buat dua surat
        $surat1 = $this->service->createSurat($data, $this->nakes->id);
        $surat2 = $this->service->createSurat($data, $this->nakes->id);

        $parts1 = explode('-', $surat1->nomor_surat);
        $parts2 = explode('-', $surat2->nomor_surat);
        $seq1 = (int) end($parts1);
        $seq2 = (int) end($parts2);

        $this->assertEquals($seq1 + 1, $seq2);

        // Hapus surat terakhir
        $this->service->deleteSurat($surat2);
        $this->assertDatabaseMissing('surat_rujukan', ['id' => $surat2->id]);

        // Buat surat baru: nomor terbit TIDAK boleh menggunakan ulang nomor $surat2
        $surat3 = $this->service->createSurat($data, $this->nakes->id);
        $parts3 = explode('-', $surat3->nomor_surat);
        $seq3 = (int) end($parts3);

        $this->assertEquals($seq2 + 1, $seq3);
    }

    public function test_period_transition_generates_new_period_sequence(): void
    {
        $nomorToday = $this->service->generateNomorSurat('20261122');
        $this->assertEquals('SR-20261122-0001', $nomorToday);

        $nomorTomorrow = $this->service->generateNomorSurat('20261123');
        $this->assertEquals('SR-20261123-0001', $nomorTomorrow);
    }

    public function test_create_surat_throws_exception_when_balita_has_no_kms(): void
    {
        $balitaTanpaKms = Balita::factory()->create(['name' => 'Balita Kosong']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Surat rujukan tidak dapat diterbitkan karena balita yang dipilih belum memiliki data hasil pengukuran KMS.');

        $this->service->createSurat([
            'balita_id' => $balitaTanpaKms->id,
            'faskes_tujuan' => 'RSUD',
            'alasan' => 'Tes tanpa KMS',
            'status_follow_up' => 'belum',
        ], $this->nakes->id);
    }

    public function test_is_retryable_collision_identifies_deadlock_and_unique_number_only(): void
    {
        // 1. Duplicate key pada nomor_surat -> retryable
        $pdoExceptionDup = new \PDOException('Duplicate entry \'SR-20261122-0001\' for key \'surat_rujukan_nomor_surat_unique\'', 23000);
        $pdoExceptionDup->errorInfo = ['23000', 1062, 'Duplicate entry \'SR-20261122-0001\' for key \'surat_rujukan_nomor_surat_unique\''];
        $queryExceptionDup = new \Illuminate\Database\QueryException('mysql', 'insert into surat_rujukan ...', [], $pdoExceptionDup);
        $this->assertTrue($this->service->isRetryableCollision($queryExceptionDup));

        // 2. Deadlock -> retryable
        $pdoExceptionDeadlock = new \PDOException('Deadlock found when trying to get lock', 40001);
        $pdoExceptionDeadlock->errorInfo = ['40001', 1213, 'Deadlock found when trying to get lock'];
        $queryExceptionDeadlock = new \Illuminate\Database\QueryException('mysql', 'update ...', [], $pdoExceptionDeadlock);
        $this->assertTrue($this->service->isRetryableCollision($queryExceptionDeadlock));

        // 3. Foreign key violation (error 1452) -> NOT retryable
        $pdoExceptionFk = new \PDOException('Cannot add or update a child row: a foreign key constraint fails', 23000);
        $pdoExceptionFk->errorInfo = ['23000', 1452, 'Cannot add or update a child row: a foreign key constraint fails'];
        $queryExceptionFk = new \Illuminate\Database\QueryException('mysql', 'insert into ...', [], $pdoExceptionFk);
        $this->assertFalse($this->service->isRetryableCollision($queryExceptionFk));
    }

    public function test_generate_pdf_contains_surat_details_and_qr(): void
    {
        $surat = $this->service->createSurat([
            'balita_id' => $this->balita->id,
            'faskes_tujuan' => 'RSUD dr. Soetomo',
            'alasan' => 'Gizi buruk klinis untuk evaluasi lanjutan',
            'status_follow_up' => 'belum',
        ], $this->nakes->id);

        $verifyUrl = route('rujukan.verify', ['code' => $surat->qr_verification_code]);
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->generate($verifyUrl);
        $latestKms = PengukuranKms::where('balita_id', $surat->balita_id)->orderByDesc('date')->orderByDesc('id')->first();

        $html = view('operasional.rujukan.pdf', compact('surat', 'qrSvg', 'verifyUrl', 'latestKms'))->render();
        $this->assertStringContainsString($surat->nomor_surat, $html);
        $this->assertStringContainsString($this->balita->name, $html);
        $this->assertStringContainsString('data:image/svg+xml;base64,', $html);
        $this->assertStringContainsString($surat->qr_verification_code, $html);

        $pdf = $this->service->generatePdf($surat);
        $this->assertInstanceOf(\Barryvdh\DomPDF\PDF::class, $pdf);

        $output = $pdf->output();
        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF-', $output);
    }

    public function test_get_saran_rujukan_respects_latest_date_over_larger_id_when_backdated(): void
    {
        $balita = Balita::factory()->create(['name' => 'Balita Backdated']);

        // Pengukuran 1: Tanggal hari ini, status Gizi Baik (id lebih kecil)
        PengukuranKms::create([
            'balita_id' => $balita->id,
            'user_id' => $this->nakes->id,
            'date' => now()->toDateString(),
            'berat_badan' => 12.0,
            'tinggi_badan' => 88.0,
            'status_gizi' => 'Gizi Baik',
        ]);

        // Pengukuran 2: Diinput belakangan (id lebih besar), tetapi tanggal mundur sebulan lalu, status Gizi Buruk
        PengukuranKms::create([
            'balita_id' => $balita->id,
            'user_id' => $this->nakes->id,
            'date' => now()->subMonth()->toDateString(),
            'berat_badan' => 7.0,
            'tinggi_badan' => 74.0,
            'status_gizi' => 'Gizi Buruk',
        ]);

        $saran = $this->service->getSaranRujukan();

        // Karena pengukuran terbaru menurut tanggal adalah Gizi Baik, balita TIDAK boleh masuk saran
        $this->assertFalse($saran->contains('id', $balita->id));
    }

    public function test_get_saran_rujukan_tie_breaks_same_date_by_highest_id(): void
    {
        $balita = Balita::factory()->create(['name' => 'Balita Same Date']);

        // Pengukuran 1 pada hari ini: Gizi Baik
        PengukuranKms::create([
            'balita_id' => $balita->id,
            'user_id' => $this->nakes->id,
            'date' => now()->toDateString(),
            'berat_badan' => 12.0,
            'tinggi_badan' => 88.0,
            'status_gizi' => 'Gizi Baik',
        ]);

        // Pengukuran 2 pada hari yang sama (koreksi/pemeriksaan ulang sore hari): Gizi Buruk (id lebih besar)
        PengukuranKms::create([
            'balita_id' => $balita->id,
            'user_id' => $this->nakes->id,
            'date' => now()->toDateString(),
            'berat_badan' => 7.0,
            'tinggi_badan' => 74.0,
            'status_gizi' => 'Gizi Buruk',
        ]);

        $saran = $this->service->getSaranRujukan();

        // Pengukuran kedua (ID lebih besar pada tanggal yang sama) harus dipilih -> masuk saran
        $this->assertTrue($saran->contains('id', $balita->id));
    }

    public function test_get_saran_rujukan_excludes_proses_and_includes_selesai(): void
    {
        // Balita A: status_follow_up = 'proses' -> harus DIKECUALIKAN
        $balitaA = Balita::factory()->create(['name' => 'Balita Sedang Proses']);
        PengukuranKms::create([
            'balita_id' => $balitaA->id,
            'user_id' => $this->nakes->id,
            'date' => now()->toDateString(),
            'berat_badan' => 6.5,
            'tinggi_badan' => 72.0,
            'status_gizi' => 'Gizi Buruk',
        ]);
        SuratRujukan::factory()->create([
            'balita_id' => $balitaA->id,
            'user_id' => $this->nakes->id,
            'status_follow_up' => 'proses',
        ]);

        // Balita B: status_follow_up = 'selesai' -> jika ada pengukuran berisiko baru, HARUS MASUK lagi
        $balitaB = Balita::factory()->create(['name' => 'Balita Selesai Rujukan Lama']);
        PengukuranKms::create([
            'balita_id' => $balitaB->id,
            'user_id' => $this->nakes->id,
            'date' => now()->toDateString(),
            'berat_badan' => 6.7,
            'tinggi_badan' => 72.0,
            'status_gizi' => 'Gizi Buruk',
        ]);
        SuratRujukan::factory()->create([
            'balita_id' => $balitaB->id,
            'user_id' => $this->nakes->id,
            'status_follow_up' => 'selesai',
        ]);

        $saran = $this->service->getSaranRujukan();

        $this->assertFalse($saran->contains('id', $balitaA->id));
        $this->assertTrue($saran->contains('id', $balitaB->id));
    }

    public function test_get_saran_rujukan_empty_when_no_data(): void
    {
        $saran = $this->service->getSaranRujukan();
        $this->assertTrue($saran->isEmpty());
    }
}
