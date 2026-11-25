<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengeluaranStokFefoRequest;
use App\Http\Requests\StoreStokGudangRequest;
use App\Http\Requests\UpdateStokGudangRequest;
use App\Models\BarangPmt;
use App\Models\DistribusiPmt;
use App\Models\StokGudang;
use App\Services\PmtStockService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class StokGudangController extends Controller
{
    protected PmtStockService $stockService;

    public function __construct(PmtStockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * [KF-27, KF-28, KF-29, KF-30] Tampilkan daftar stok gudang terurut metode FEFO.
     */
    public function index(Request $request): View
    {
        $query = StokGudang::with('barang');

        // Pencarian nomor batch atau nama barang
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('no_batch', 'like', "%{$search}%")
                  ->orWhereHas('barang', function ($bq) use ($search) {
                      $bq->where('nama', 'like', "%{$search}%")
                         ->orWhere('kategori', 'like', "%{$search}%");
                  });
            });
        }

        // Filter Kategori Barang
        if ($kategori = $request->input('kategori')) {
            if ($kategori !== 'all') {
                $query->whereHas('barang', fn ($bq) => $bq->where('kategori', $kategori));
            }
        }

        // Filter Status (FEFO / Hampir ED / Stok Kritis)
        $statusFilter = $request->input('status', 'all');
        $today = Carbon::today();

        if ($statusFilter === 'hampir_ed') {
            $query->where('stock_qty', '>', 0)
                  ->whereDate('exp_date', '>=', $today)
                  ->whereDate('exp_date', '<=', $today->copy()->addDays(PmtStockService::NEAR_EXPIRY_DAYS));
        } elseif ($statusFilter === 'stok_kritis') {
            $query->where('stock_qty', '>', 0)
                  ->whereRaw('stock_qty <= (SELECT min_batas_stok FROM barang_pmt WHERE barang_pmt.id = stok_gudang.barang_id)');
        } elseif ($statusFilter === 'expired') {
            $query->whereDate('exp_date', '<', $today);
        } elseif ($statusFilter === 'prioritas_fefo') {
            // Ambil ID batch prioritas pertama (exp_date paling awal) untuk tiap barang
            $priorityIds = StokGudang::where('stock_qty', '>', 0)
                ->whereDate('exp_date', '>=', $today)
                ->orderBy('exp_date', 'asc')
                ->orderBy('date_in', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->groupBy('barang_id')
                ->map(fn ($group) => $group->first()->id)
                ->values()
                ->all();

            $query->whereIn('id', $priorityIds);
        }

        // Urutkan utama berdasarkan FEFO (exp_date terdekat lebih dulu)
        $stoks = $query->orderByRaw('CASE WHEN stock_qty > 0 AND exp_date >= ? THEN 0 ELSE 1 END ASC', [$today->toDateString()])
            ->orderBy('exp_date', 'asc')
            ->orderBy('date_in', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString();

        // Pre-fetch antrean aktif per barang untuk menentukan badge status FEFO secara akurat
        $activeQueuesByBarang = StokGudang::with('barang')
            ->where('stock_qty', '>', 0)
            ->whereDate('exp_date', '>=', $today)
            ->orderBy('exp_date', 'asc')
            ->orderBy('date_in', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy('barang_id');

        $barangs = BarangPmt::orderBy('nama')->get()->map(function (BarangPmt $b) use ($activeQueuesByBarang) {
            $queue = $activeQueuesByBarang->get($b->id, collect());
            $b->saldo_otomatis = round((float) $queue->sum('stock_qty'), 2);
            $b->prioritas_batch = $queue->first()?->no_batch;
            $b->prioritas_exp = $queue->first()?->exp_date?->format('d M Y');

            return $b;
        });

        $kategoris = BarangPmt::select('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        $stats = $this->stockService->getWarehouseSummaryStats();

        return view('pmt.stok.index', [
            'stoks' => $stoks,
            'barangs' => $barangs,
            'kategoris' => $kategoris,
            'stats' => $stats,
            'activeQueuesByBarang' => $activeQueuesByBarang,
            'stockService' => $this->stockService,
        ]);
    }

    /**
     * [KF-27 & KF-29] Simpan batch stok gudang baru dan perbarui saldo otomatis.
     */
    public function store(StoreStokGudangRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $stok = $this->stockService->recordStockIn($validated);
        $totalSaldo = $this->stockService->getTotalSaldoBarang((int) $stok->barang_id);

        return redirect()
            ->route('pmt.stok.index')
            ->with(
                'success',
                "Batch '{$stok->no_batch}' untuk {$stok->barang->nama} berhasil ditambahkan (+{$stok->stock_qty} {$stok->barang->unit_type}). Saldo stok aktif saat ini: {$totalSaldo} {$stok->barang->unit_type}."
            );
    }

    /**
     * [KF-27 & KF-29] Perbarui data batch stok gudang.
     */
    public function update(UpdateStokGudangRequest $request, StokGudang $stok): RedirectResponse
    {
        $validated = $request->validated();

        $stok->update([
            'barang_id' => $validated['barang_id'],
            'no_batch' => strtoupper(trim($validated['no_batch'])),
            'date_in' => $validated['date_in'],
            'exp_date' => $validated['exp_date'],
            'stock_qty' => round((float) $validated['stock_qty'], 2),
        ]);

        $stok->load('barang');
        $totalSaldo = $this->stockService->getTotalSaldoBarang((int) $stok->barang_id);

        return redirect()
            ->route('pmt.stok.index')
            ->with(
                'success',
                "Data batch '{$stok->no_batch}' ({$stok->barang->nama}) berhasil diperbarui. Saldo stok aktif: {$totalSaldo} {$stok->barang->unit_type}."
            );
    }

    /**
     * [KF-27] Hapus batch stok gudang (dengan proteksi integritas riwayat distribusi).
     */
    public function destroy(StokGudang $stok): RedirectResponse
    {
        $noBatch = $stok->no_batch;
        $namaBarang = $stok->barang?->nama ?? 'Barang PMT';

        $hasDistributionHistory = $stok->detailDistribusiStok()->exists();

        if ($hasDistributionHistory) {
            $stok->update(['stock_qty' => 0]);

            return redirect()
                ->route('pmt.stok.index')
                ->with(
                    'warning',
                    "Batch '{$noBatch}' ({$namaBarang}) sudah tercatat dalam riwayat distribusi. Saldo batch dikosongkan menjadi 0 demi menjaga integritas audit."
                );
        }

        $stok->delete();

        return redirect()
            ->route('pmt.stok.index')
            ->with('success', "Batch '{$noBatch}' ({$namaBarang}) berhasil dihapus dari gudang.");
    }

    /**
     * [KF-28 & KF-29] Pengeluaran stok otomatis menggunakan algoritma FEFO.
     */
    public function keluarkanFefo(PengeluaranStokFefoRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $barang = BarangPmt::findOrFail($validated['barang_id']);
        $qtyNeeded = (float) $validated['jumlah_keluar'];

        try {
            $allocations = $this->stockService->deductStockFefo($barang->id, $qtyNeeded);
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->route('pmt.stok.index')
                ->withErrors(['jumlah_keluar' => $e->getMessage()])
                ->with('warning', $e->getMessage());
        }

        $batchSummary = collect($allocations)
            ->map(fn (array $a) => "{$a['no_batch']} (-{$a['allocated_qty']} {$barang->unit_type})")
            ->join(', ');

        $saldoAkhir = $this->stockService->getTotalSaldoBarang($barang->id);

        return redirect()
            ->route('pmt.stok.index')
            ->with(
                'success',
                "Pengeluaran FEFO {$barang->nama} sejumlah {$qtyNeeded} {$barang->unit_type} berhasil diproses dari batch: {$batchSummary}. Saldo stok otomatis tersisa: {$saldoAkhir} {$barang->unit_type}."
            );
    }

    /**
     * [KF-28] Endpoint JSON untuk simulasi/preview alokasi batch metode FEFO.
     */
    public function previewFefo(Request $request): JsonResponse
    {
        $barangId = (int) $request->input('barang_id');
        $qtyNeeded = (float) $request->input('jumlah_keluar');

        if ($barangId <= 0 || $qtyNeeded <= 0) {
            return response()->json([
                'valid' => false,
                'message' => 'Pilih barang dan masukkan jumlah pengeluaran lebih dari 0.',
                'allocations' => [],
            ]);
        }

        try {
            $allocations = $this->stockService->allocateFefo($barangId, $qtyNeeded);
            $saldoTersedia = $this->stockService->getTotalSaldoBarang($barangId);

            return response()->json([
                'valid' => true,
                'saldo_tersedia' => $saldoTersedia,
                'saldo_setelah' => round($saldoTersedia - $qtyNeeded, 2),
                'allocations' => $allocations,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'valid' => false,
                'message' => $e->getMessage(),
                'saldo_tersedia' => $this->stockService->getTotalSaldoBarang($barangId),
                'allocations' => [],
            ], 422);
        }
    }

    /**
     * [KF-31] Endpoint pencarian / pemindaian barcode nomor batch.
     */
    public function scanBatch(Request $request): JsonResponse
    {
        $code = trim((string) $request->input('code'));

        if ($code === '') {
            return response()->json([
                'found' => false,
                'message' => 'Kode barcode batch tidak boleh kosong.',
            ], 422);
        }

        $stok = StokGudang::with('barang')
            ->where('no_batch', $code)
            ->orWhere('no_batch', 'like', "%{$code}%")
            ->orderBy('exp_date', 'asc')
            ->first();

        if (! $stok) {
            return response()->json([
                'found' => false,
                'message' => "Batch dengan kode barcode '{$code}' tidak ditemukan di gudang.",
            ], 404);
        }

        $fefoStatus = $this->stockService->determineFefoStatus($stok);
        $daysLeft = $this->stockService->getDaysUntilExpiry($stok);

        return response()->json([
            'found' => true,
            'batch' => [
                'id' => $stok->id,
                'no_batch' => $stok->no_batch,
                'barang_id' => $stok->barang_id,
                'barang_nama' => $stok->barang?->nama,
                'kategori' => $stok->barang?->kategori,
                'unit_type' => $stok->barang?->unit_type,
                'date_in' => $stok->date_in->format('Y-m-d'),
                'exp_date' => $stok->exp_date->format('Y-m-d'),
                'stock_qty' => (float) $stok->stock_qty,
                'days_until_expiry' => $daysLeft,
                'is_near_expiry' => $this->stockService->isNearExpiry($stok),
                'is_critical' => $this->stockService->isCriticalStock($stok),
                'fefo_status' => $fefoStatus,
                'total_saldo_barang' => $this->stockService->getTotalSaldoBarang((int) $stok->barang_id),
            ],
        ]);
    }
}
