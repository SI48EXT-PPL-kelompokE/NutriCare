<?php

namespace App\Services;

use App\Models\Balita;
use App\Models\PengukuranKms;
use App\Models\SuratRujukan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SuratRujukanService
{
    /**
     * Status gizi yang mengindikasikan balita perlu dirujuk.
     * Menggunakan kategori yang sudah ada di field status_gizi KMS.
     */
    public const STATUS_PERLU_RUJUKAN = [
        'Gizi Buruk',
        'gizi_buruk',
        'Stunting',
        'stunting',
        'Wasting',
        'wasting',
        'Severely Underweight',
        'Severely Stunted',
        'Severely Wasted',
    ];

    /**
     * Alokasikan nomor surat berikutnya secara atomik dan persisten.
     * Menggunakan counter persisten per periode (YYYYMMDD) dengan penguncian atomik (lockForUpdate).
     * Format: SR-YYYYMMDD-XXXX (contoh: SR-20261122-0001, di atas 9999 tetap valid: SR-20261122-10000).
     * Nomor yang telah dialokasikan bersifat monotonic dan tidak dapat digunakan ulang meskipun surat terakhir dihapus.
     */
    public function generateNomorSurat(?string $period = null): string
    {
        $period = $period ?: now()->format('Ymd');
        $datePrefix = "SR-{$period}-";

        return DB::transaction(function () use ($period, $datePrefix) {
            $counter = DB::table('surat_rujukan_counters')
                ->where('period', $period)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                // Inisialisasi counter jika belum ada baris periode hari ini.
                // Tangani nomor surat lama (misal data existing atau seeded sebelum tabel counter dibuat).
                // Gunakan parsing numerik untuk mencari nilai sekuens terbesar agar tidak terpengaruh urutan teks/alfabetis.
                $existingSurat = DB::table('surat_rujukan')
                    ->where('nomor_surat', 'like', "{$datePrefix}%")
                    ->pluck('nomor_surat');

                $maxExistingSeq = 0;
                foreach ($existingSurat as $nomor) {
                    $parts = explode('-', $nomor);
                    $seq = (int) end($parts);
                    if ($seq > $maxExistingSeq) {
                        $maxExistingSeq = $seq;
                    }
                }

                // insertOrIgnore aman terhadap inisialisasi serentak dari transaksi paralel
                DB::table('surat_rujukan_counters')->insertOrIgnore([
                    'period' => $period,
                    'last_sequence' => $maxExistingSeq,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $counter = DB::table('surat_rujukan_counters')
                    ->where('period', $period)
                    ->lockForUpdate()
                    ->first();
            }

            $nextSeq = (int) $counter->last_sequence + 1;

            DB::table('surat_rujukan_counters')
                ->where('period', $period)
                ->update([
                    'last_sequence' => $nextSeq,
                    'updated_at' => now(),
                ]);

            return $datePrefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Memeriksa apakah error database merupakan benturan alokasi atau deadlock yang dapat di-retry.
     * Tidak menganggap semua SQLSTATE 23000 sebagai benturan nomor.
     */
    public function isRetryableCollision(QueryException $e): bool
    {
        $errorCode = (int) ($e->errorInfo[1] ?? 0);
        $sqlState = (string) ($e->errorInfo[0] ?? $e->getCode());
        $message = $e->getMessage();

        // 1. MySQL 1213: Deadlock
        if ($errorCode === 1213 || $sqlState === '40001') {
            return true;
        }

        // 2. MySQL 1205: Lock wait timeout
        if ($errorCode === 1205) {
            return true;
        }

        // 3. MySQL 1062: Duplicate entry khusus pada nomor_surat atau qr_verification_code
        if ($errorCode === 1062 && (
            str_contains($message, 'nomor_surat') ||
            str_contains($message, 'surat_rujukan_nomor_surat_unique') ||
            str_contains($message, 'qr_verification_code')
        )) {
            return true;
        }

        return false;
    }

    /**
     * Buat surat rujukan baru.
     */
    public function createSurat(array $data, int $userId): SuratRujukan
    {
        // Tegakkan prasyarat KMS di service layer: balita wajib memiliki hasil pengukuran KMS
        $hasKms = PengukuranKms::where('balita_id', $data['balita_id'])->exists();
        if (!$hasKms) {
            throw new \InvalidArgumentException('Surat rujukan tidak dapat diterbitkan karena balita yang dipilih belum memiliki data hasil pengukuran KMS.');
        }

        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                return DB::transaction(function () use ($data, $userId) {
                    $nomorSurat = $this->generateNomorSurat();
                    $qrCode = Str::uuid()->toString();

                    return SuratRujukan::create([
                        'balita_id' => $data['balita_id'],
                        'user_id' => $userId,
                        'nomor_surat' => $nomorSurat,
                        'faskes_tujuan' => $data['faskes_tujuan'],
                        'alasan' => $data['alasan'],
                        'status_follow_up' => $data['status_follow_up'] ?? 'belum',
                        'qr_verification_code' => $qrCode,
                    ]);
                });
            } catch (QueryException $e) {
                if ($this->isRetryableCollision($e) && $attempts < $maxAttempts) {
                    usleep(50000 * $attempts);
                    continue;
                }

                throw $e;
            }
        }

        throw new \RuntimeException('Gagal menerbitkan surat rujukan unik setelah mencapai batas maksimal percobaan.');
    }

    /**
     * Update surat rujukan. Nomor surat dan QR tidak diubah.
     */
    public function updateSurat(SuratRujukan $surat, array $data): SuratRujukan
    {
        $updateData = [
            'faskes_tujuan' => $data['faskes_tujuan'],
            'alasan' => $data['alasan'],
        ];

        if (isset($data['status_follow_up'])) {
            $updateData['status_follow_up'] = $data['status_follow_up'];
        }

        $surat->update($updateData);

        return $surat->fresh();
    }

    /**
     * Hapus surat rujukan.
     */
    public function deleteSurat(SuratRujukan $surat): bool
    {
        return (bool) $surat->delete();
    }

    /**
     * Generate PDF surat rujukan dengan QR verifikasi.
     */
    public function generatePdf(SuratRujukan $surat)
    {
        $surat->load(['balita.orangTua', 'nakes']);

        // Generate QR code sebagai SVG inline
        $verifyUrl = route('rujukan.verify', ['code' => $surat->qr_verification_code]);
        $qrSvg = QrCode::size(150)->generate($verifyUrl);

        // Ambil pengukuran KMS terakhir balita
        $latestKms = PengukuranKms::where('balita_id', $surat->balita_id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->first();

        $pdf = Pdf::loadView('operasional.rujukan.pdf', compact('surat', 'qrSvg', 'verifyUrl', 'latestKms'));
        $pdf->setPaper('A4');

        return $pdf;
    }

    /**
     * Verifikasi keaslian surat berdasarkan QR verification code.
     */
    public function verifySurat(string $code): ?SuratRujukan
    {
        return SuratRujukan::with(['balita', 'nakes'])
            ->where('qr_verification_code', $code)
            ->first();
    }

    /**
     * Dapatkan daftar balita yang disarankan perlu dirujuk
     * berdasarkan status gizi dari pengukuran KMS terakhir.
     * Tidak mengarang threshold klinis — menggunakan field status_gizi yang sudah ada.
     */
    public function getSaranRujukan(): \Illuminate\Database\Eloquent\Collection
    {
        // Ambil ID pengukuran KMS terbaru tiap balita:
        // Prioritas: MAX(date), dengan tie-break deterministik MAX(id) jika ada lebih dari 1 pengukuran pada tanggal yang sama.
        $latestKmsIds = PengukuranKms::select(DB::raw('MAX(p.id) as id'))
            ->from('pengukuran_kms as p')
            ->joinSub(
                PengukuranKms::select('balita_id', DB::raw('MAX(date) as max_date'))
                    ->groupBy('balita_id'),
                'latest_dates',
                function ($join) {
                    $join->on('p.balita_id', '=', 'latest_dates.balita_id')
                         ->on('p.date', '=', 'latest_dates.max_date');
                }
            )
            ->groupBy('p.balita_id')
            ->pluck('id');

        if ($latestKmsIds->isEmpty()) {
            return Balita::query()->whereRaw('1=0')->get();
        }

        // Cari balita dengan status gizi yang mengindikasikan perlu rujukan
        $balitaIds = PengukuranKms::whereIn('id', $latestKmsIds)
            ->where(function ($q) {
                $q->whereIn('status_gizi', self::STATUS_PERLU_RUJUKAN);
                // Juga tangkap z_score_bbu sangat rendah (< -3) jika status_gizi belum diisi
                $q->orWhere(function ($sq) {
                    $sq->whereNull('status_gizi')
                        ->where('z_score_bbu', '<', -3);
                });
            })
            ->pluck('balita_id');

        // Exclude balita yang sudah memiliki surat rujukan aktif (belum atau proses follow up)
        $alreadyReferred = SuratRujukan::whereIn('balita_id', $balitaIds)
            ->whereIn('status_follow_up', ['belum', 'proses'])
            ->pluck('balita_id');

        return Balita::with(['orangTua', 'pengukuranKms' => function ($q) {
            $q->orderByDesc('date')->orderByDesc('id');
        }])
            ->whereIn('id', $balitaIds->diff($alreadyReferred)->values()->all())
            ->get();
    }
}
