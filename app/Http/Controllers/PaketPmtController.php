<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaketPmtRequest;
use App\Http\Requests\UpdatePaketPmtRequest;
use App\Models\BarangPmt;
use App\Models\KomposisiPaketPmt;
use App\Models\PaketPmt;
use App\Services\PmtNutritionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaketPmtController extends Controller
{
    protected PmtNutritionService $nutritionService;

    public function __construct(PmtNutritionService $nutritionService)
    {
        $this->nutritionService = $nutritionService;
    }

    /**
     * Tampilkan daftar master paket PMT.
     */
    public function index(Request $request): View
    {
        $query = PaketPmt::with(['komposisiPaketPmt.barang']);

        // Filter Pencarian
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_paket_pmt', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // Filter Status
        if ($request->has('status') && $request->input('status') !== '' && $request->input('status') !== 'all') {
            $query->where('status', (bool) $request->input('status'));
        }

        // Filter Rentang Usia Target
        if ($targetAge = $request->input('age_target')) {
            $age = (int) $targetAge;
            $query->where('min_age_target', '<=', $age)
                  ->where('max_age_target', '>=', $age);
        }

        $pakets = $query->latest()->paginate(9)->withQueryString();

        // Statistik Ringkas untuk Dashboard PMT
        $stats = [
            'total' => PaketPmt::count(),
            'active' => PaketPmt::where('status', true)->count(),
            'inactive' => PaketPmt::where('status', false)->count(),
            'avg_calories' => round((float) PaketPmt::where('status', true)->avg('total_energi_kcal'), 1),
        ];

        return view('pmt.paket.index', compact('pakets', 'stats'));
    }

    /**
     * Tampilkan form pembuatan paket PMT baru.
     */
    public function create(): View
    {
        $barangs = BarangPmt::orderBy('nama')->get();

        return view('pmt.paket.create', compact('barangs'));
    }

    /**
     * Simpan paket PMT baru dan komposisinya.
     */
    public function store(StorePaketPmtRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Hitung akumulasi nutrisi otomatis berdasarkan komposisi
        $totals = $this->nutritionService->calculateTotals($validated['komposisi']);

        DB::transaction(function () use ($validated, $totals) {
            $paket = PaketPmt::create([
                'nama_paket_pmt' => $validated['nama_paket_pmt'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'min_age_target' => $validated['min_age_target'],
                'max_age_target' => $validated['max_age_target'],
                'unit_type' => $validated['unit_type'],
                'status' => $validated['status'] ?? true,
                'total_energi_kcal' => $totals['total_energi_kcal'],
                'total_protein' => $totals['total_protein'],
                'total_lemak' => $totals['total_lemak'],
                'total_karbohidrat' => $totals['total_karbohidrat'],
            ]);

            foreach ($validated['komposisi'] as $item) {
                KomposisiPaketPmt::create([
                    'paket_id' => $paket->id,
                    'barang_id' => $item['barang_id'],
                    'jumlah' => $item['jumlah'],
                    'unit_type' => $item['unit_type'],
                ]);
            }
        });

        return redirect()
            ->route('pmt.paket.index')
            ->with('success', "Paket PMT '{$validated['nama_paket_pmt']}' berhasil dibuat dengan total {$totals['total_energi_kcal']} Kcal.");
    }

    /**
     * Tampilkan detail paket PMT beserta rincian gizi bahan.
     */
    public function show(PaketPmt $paket): View
    {
        $paket->load(['komposisiPaketPmt.barang']);
        $distribusiCount = $paket->distribusiPmt()->count();

        // Hitung persentase makronutrien untuk visualisasi gizi
        $totalMakro = $paket->total_protein + $paket->total_lemak + $paket->total_karbohidrat;
        $persenProtein = $totalMakro > 0 ? round(($paket->total_protein / $totalMakro) * 100, 1) : 0;
        $persenLemak = $totalMakro > 0 ? round(($paket->total_lemak / $totalMakro) * 100, 1) : 0;
        $persenKarbo = $totalMakro > 0 ? round(($paket->total_karbohidrat / $totalMakro) * 100, 1) : 0;

        return view('pmt.paket.show', compact('paket', 'distribusiCount', 'persenProtein', 'persenLemak', 'persenKarbo'));
    }

    /**
     * Tampilkan form untuk mengubah paket PMT.
     */
    public function edit(PaketPmt $paket): View
    {
        $paket->load(['komposisiPaketPmt.barang']);
        $barangs = BarangPmt::orderBy('nama')->get();

        return view('pmt.paket.edit', compact('paket', 'barangs'));
    }

    /**
     * Perbarui paket PMT beserta komposisinya.
     */
    public function update(UpdatePaketPmtRequest $request, PaketPmt $paket): RedirectResponse
    {
        $validated = $request->validated();

        // Hitung ulang akumulasi nutrisi otomatis
        $totals = $this->nutritionService->calculateTotals($validated['komposisi']);

        DB::transaction(function () use ($paket, $validated, $totals) {
            $paket->update([
                'nama_paket_pmt' => $validated['nama_paket_pmt'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'min_age_target' => $validated['min_age_target'],
                'max_age_target' => $validated['max_age_target'],
                'unit_type' => $validated['unit_type'],
                'status' => $validated['status'] ?? false,
                'total_energi_kcal' => $totals['total_energi_kcal'],
                'total_protein' => $totals['total_protein'],
                'total_lemak' => $totals['total_lemak'],
                'total_karbohidrat' => $totals['total_karbohidrat'],
            ]);

            // Hapus komposisi lama dan simpan komposisi yang baru
            $paket->komposisiPaketPmt()->delete();

            foreach ($validated['komposisi'] as $item) {
                KomposisiPaketPmt::create([
                    'paket_id' => $paket->id,
                    'barang_id' => $item['barang_id'],
                    'jumlah' => $item['jumlah'],
                    'unit_type' => $item['unit_type'],
                ]);
            }
        });

        return redirect()
            ->route('pmt.paket.show', $paket)
            ->with('success', "Paket PMT '{$paket->nama_paket_pmt}' berhasil diperbarui.");
    }

    /**
     * Hapus atau nonaktifkan paket PMT.
     */
    public function destroy(PaketPmt $paket): RedirectResponse
    {
        $namaPaket = $paket->nama_paket_pmt;

        // Proteksi integritas relasi: Cek apakah paket sudah pernah didistribusikan
        if ($paket->distribusiPmt()->exists()) {
            $paket->update(['status' => false]);

            return redirect()
                ->route('pmt.paket.index')
                ->with('warning', "Paket PMT '{$namaPaket}' sudah memiliki riwayat distribusi. Paket tidak dihapus permanen, melainkan dinonaktifkan demi integritas audit.");
        }

        DB::transaction(function () use ($paket) {
            $paket->komposisiPaketPmt()->delete();
            $paket->delete();
        });

        return redirect()
            ->route('pmt.paket.index')
            ->with('success', "Paket PMT '{$namaPaket}' berhasil dihapus.");
    }

    /**
     * Ubah status aktif/nonaktif paket secara cepat.
     */
    public function toggleStatus(PaketPmt $paket): RedirectResponse
    {
        $newStatus = !$paket->status;
        $paket->update(['status' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status paket '{$paket->nama_paket_pmt}' berhasil {$statusText}.");
    }

    /**
     * Endpoint API untuk preview kalkulasi nutrisi secara dinamis dari client.
     */
    public function calculatePreview(Request $request)
    {
        $items = $request->input('komposisi', []);
        if (!is_array($items)) {
            $items = [];
        }

        $totals = $this->nutritionService->calculateTotals($items);

        return response()->json($totals);
    }
}
