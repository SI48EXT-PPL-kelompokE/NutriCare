<?php

namespace App\Services;

use App\Models\BarangPmt;
use App\Models\DetailDistribusiStok;
use App\Models\DistribusiPmt;
use App\Models\StokGudang;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PmtStockService
{
    public const NEAR_EXPIRY_DAYS = 30;

    /**
     * [KF-28] Ambil antrean batch aktif untuk suatu barang dengan urutan FEFO
     * (First Expired, First Out: exp_date terdekat lebih dulu).
     */
    public function getFefoQueueForBarang(int $barangId): Collection
    {
        return StokGudang::with('barang')
            ->where('barang_id', $barangId)
            ->where('stock_qty', '>', 0)
            ->whereDate('exp_date', '>=', Carbon::today())
            ->orderBy('exp_date', 'asc')
            ->orderBy('date_in', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * [KF-28] Tentukan status antrean FEFO untuk suatu batch stok.
     * Mengembalikan kode status dan label tampilan sesuai desain Figma.
     */
    public function determineFefoStatus(StokGudang $stok, ?Collection $activeBatchesForBarang = null): array
    {
        if ((float) $stok->stock_qty <= 0) {
            return [
                'code' => 'habis',
                'label' => 'Stok Habis',
            ];
        }

        if ($this->isExpired($stok)) {
            return [
                'code' => 'expired',
                'label' => 'Kedaluwarsa',
            ];
        }

        $queue = $activeBatchesForBarang ?? $this->getFefoQueueForBarang((int) $stok->barang_id);
        $sortedIds = $queue->pluck('id')->values();
        $position = $sortedIds->search($stok->id);

        if ($position === 0) {
            return [
                'code' => 'prioritas_fefo',
                'label' => 'Prioritas FEFO',
            ];
        }

        if ($position === 1) {
            return [
                'code' => 'antrean_berikutnya',
                'label' => 'Antrean Berikutnya',
            ];
        }

        return [
            'code' => 'stok_aman',
            'label' => 'Stok Aman',
        ];
    }

    /**
     * [KF-28] Simulasi alokasi pengeluaran stok menggunakan metode FEFO.
     *
     * @throws InvalidArgumentException
     */
    public function allocateFefo(int $barangId, float $qtyNeeded): array
    {
        if ($qtyNeeded <= 0) {
            throw new InvalidArgumentException('Jumlah pengeluaran stok harus lebih dari 0.');
        }

        $queue = $this->getFefoQueueForBarang($barangId);
        $totalAvailable = (float) $queue->sum('stock_qty');

        if ($totalAvailable < $qtyNeeded) {
            throw new InvalidArgumentException(
                "Stok aktif tidak mencukupi. Tersedia: {$totalAvailable}, dibutuhkan: {$qtyNeeded}."
            );
        }

        $remainingToAllocate = $qtyNeeded;
        $allocations = [];

        foreach ($queue as $batch) {
            if ($remainingToAllocate <= 0) {
                break;
            }

            $batchQty = (float) $batch->stock_qty;
            $taken = min($batchQty, $remainingToAllocate);
            $remainingAfter = round($batchQty - $taken, 2);

            $allocations[] = [
                'stok_id' => $batch->id,
                'no_batch' => $batch->no_batch,
                'exp_date' => $batch->exp_date->format('Y-m-d'),
                'allocated_qty' => round($taken, 2),
                'remaining_qty' => $remainingAfter,
            ];

            $remainingToAllocate = round($remainingToAllocate - $taken, 2);
        }

        return $allocations;
    }

    /**
     * [KF-28 & KF-29] Eksekusi pengeluaran stok otomatis dengan metode FEFO
     * dan pembaruan saldo stok otomatis di dalam database transaction.
     *
     * @throws InvalidArgumentException
     */
    public function deductStockFefo(int $barangId, float $qtyNeeded): array
    {
        return DB::transaction(function () use ($barangId, $qtyNeeded) {
            $allocations = $this->allocateFefo($barangId, $qtyNeeded);

            foreach ($allocations as $allocation) {
                $stok = StokGudang::lockForUpdate()->findOrFail($allocation['stok_id']);
                $newQty = max(0, round((float) $stok->stock_qty - $allocation['allocated_qty'], 2));
                $stok->update([
                    'stock_qty' => $newQty,
                ]);
            }

            return $allocations;
        });
    }

    /**
     * [KF-27 & KF-29] Catat stok masuk atau tambahkan ke batch yang sudah ada,
     * serta perbarui saldo stok secara otomatis.
     */
    public function recordStockIn(array $validated): StokGudang
    {
        return DB::transaction(function () use ($validated) {
            $existing = StokGudang::where('barang_id', $validated['barang_id'])
                ->where('no_batch', $validated['no_batch'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->update([
                    'date_in' => $validated['date_in'],
                    'exp_date' => $validated['exp_date'],
                    'stock_qty' => round((float) $existing->stock_qty + (float) $validated['stock_qty'], 2),
                ]);

                return $existing->fresh('barang');
            }

            return StokGudang::create([
                'barang_id' => $validated['barang_id'],
                'no_batch' => strtoupper(trim($validated['no_batch'])),
                'date_in' => $validated['date_in'],
                'exp_date' => $validated['exp_date'],
                'stock_qty' => round((float) $validated['stock_qty'], 2),
            ])->load('barang');
        });
    }

    /**
     * [KF-29] Hitung total saldo stok otomatis untuk suatu barang.
     */
    public function getTotalSaldoBarang(int $barangId, bool $excludeExpired = true): float
    {
        $query = StokGudang::where('barang_id', $barangId)
            ->where('stock_qty', '>', 0);

        if ($excludeExpired) {
            $query->whereDate('exp_date', '>=', Carbon::today());
        }

        return round((float) $query->sum('stock_qty'), 2);
    }

    /**
     * [KF-30] Hitung sisa hari menuju tanggal kedaluwarsa.
     */
    public function getDaysUntilExpiry(StokGudang $stok): int
    {
        $today = Carbon::today();
        $expDate = Carbon::parse($stok->exp_date)->startOfDay();

        return (int) $today->diffInDays($expDate, false);
    }

    /**
     * [KF-30] Cek apakah batch sudah melewati tanggal kedaluwarsa.
     */
    public function isExpired(StokGudang $stok): bool
    {
        return $this->getDaysUntilExpiry($stok) < 0;
    }

    /**
     * [KF-30] Cek apakah batch hampir kedaluwarsa (sisa <= 30 hari dan belum expired).
     */
    public function isNearExpiry(StokGudang $stok, int $thresholdDays = self::NEAR_EXPIRY_DAYS): bool
    {
        if ((float) $stok->stock_qty <= 0) {
            return false;
        }

        $days = $this->getDaysUntilExpiry($stok);

        return $days >= 0 && $days <= $thresholdDays;
    }

    /**
     * [KF-30] Cek apakah kuantitas batch berada pada atau di bawah batas stok kritis barang.
     */
    public function isCriticalStock(StokGudang $stok): bool
    {
        $minBatas = (float) ($stok->barang?->min_batas_stok ?? 0);

        return (float) $stok->stock_qty <= $minBatas;
    }

    /**
     * [KF-29 & KF-30] Ringkasan statistik KPI gudang untuk kartu header sesuai Figma.
     */
    public function getWarehouseSummaryStats(): array
    {
        $today = Carbon::today();
        $nearExpiryLimit = $today->copy()->addDays(self::NEAR_EXPIRY_DAYS);

        $activeBatches = StokGudang::with('barang')
            ->where('stock_qty', '>', 0)
            ->whereDate('exp_date', '>=', $today)
            ->get();

        $totalStokTersedia = round((float) $activeBatches->sum('stock_qty'), 2);

        $nearExpiryBatches = $activeBatches->filter(fn (StokGudang $s) => $this->isNearExpiry($s));
        $mendekatiKedaluwarsaQty = round((float) $nearExpiryBatches->sum('stock_qty'), 2);

        $criticalBatches = $activeBatches->filter(fn (StokGudang $s) => $this->isCriticalStock($s));

        $tersalurkanBulanIni = round(
            (float) DistribusiPmt::whereYear('handover_date', $today->year)
                ->whereMonth('handover_date', $today->month)
                ->sum('jumlah'),
            2
        );

        if ($tersalurkanBulanIni <= 0) {
            $tersalurkanBulanIni = round(
                (float) DetailDistribusiStok::whereYear('created_at', $today->year)
                    ->whereMonth('created_at', $today->month)
                    ->sum('jumlah_diambil'),
                2
            );
        }

        return [
            'total_stok_tersedia' => $totalStokTersedia,
            'total_batch_aktif' => $activeBatches->count(),
            'mendekati_kedaluwarsa_qty' => $mendekatiKedaluwarsaQty,
            'mendekati_kedaluwarsa_batches' => $nearExpiryBatches->count(),
            'stok_kritis_batches' => $criticalBatches->count(),
            'tersalurkan_bulan_ini' => $tersalurkanBulanIni,
        ];
    }
}
