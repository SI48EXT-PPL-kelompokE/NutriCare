<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSuratRujukanRequest;
use App\Http\Requests\UpdateSuratRujukanRequest;
use App\Models\Balita;
use App\Models\PengukuranKms;
use App\Models\SuratRujukan;
use App\Services\SuratRujukanService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SuratRujukanController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected SuratRujukanService $suratRujukanService
    ) {}

    /**
     * Tampilkan daftar riwayat surat rujukan dan saran balita yang perlu dirujuk.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', SuratRujukan::class);

        $query = SuratRujukan::with(['balita.orangTua', 'nakes']);

        if ($request->filled('status')) {
            $query->where('status_follow_up', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_surat', 'like', "%{$search}%")
                    ->orWhere('faskes_tujuan', 'like', "%{$search}%")
                    ->orWhereHas('balita', function ($bq) use ($search) {
                        $bq->where('name', 'like', "%{$search}%")
                            ->orWhereHas('orangTua', function ($oq) use ($search) {
                                $oq->where('name', 'like', "%{$search}%")
                                    ->orWhere('nik', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $rujukanList = $query->latest()->paginate(15)->withQueryString();
        $saranRujukan = $this->suratRujukanService->getSaranRujukan();
        $totalEvaluatedKms = PengukuranKms::where(function ($q) {
            $q->whereNotNull('status_gizi')
              ->orWhereNotNull('z_score_bbu');
        })->count();

        if ($request->wantsJson()) {
            return response()->json([
                'rujukan' => $rujukanList,
                'saran_rujukan' => $saranRujukan,
                'total_evaluated_kms' => $totalEvaluatedKms,
            ]);
        }

        return view('operasional.rujukan.index', compact('rujukanList', 'saranRujukan', 'totalEvaluatedKms'));
    }

    /**
     * Form pembuatan surat rujukan.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', SuratRujukan::class);

        $selectedBalitaId = $request->query('balita_id');
        $balitaList = Balita::with(['orangTua', 'pengukuranKms' => fn ($q) => $q->orderByDesc('date')->orderByDesc('id')])->orderBy('name')->get();
        $selectedBalita = $selectedBalitaId ? Balita::with(['orangTua', 'pengukuranKms' => fn ($q) => $q->orderByDesc('date')->orderByDesc('id')])->find($selectedBalitaId) : null;

        return view('operasional.rujukan.create', compact('balitaList', 'selectedBalitaId', 'selectedBalita'));
    }

    /**
     * Simpan surat rujukan baru.
     */
    public function store(StoreSuratRujukanRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', SuratRujukan::class);

        try {
            $surat = $this->suratRujukanService->createSurat(
                $request->validated(),
                auth()->id()
            );
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->withInput()->withErrors(['balita_id' => $e->getMessage()]);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Gagal menerbitkan surat rujukan: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->withErrors(['error' => 'Gagal menerbitkan surat rujukan: ' . $e->getMessage()]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Surat rujukan berhasil diterbitkan.',
                'data' => $surat,
            ], 201);
        }

        return redirect()->route('rujukan.show', $surat)
            ->with('success', "Surat rujukan {$surat->nomor_surat} berhasil diterbitkan.");
    }

    /**
     * Tampilkan detail surat rujukan.
     */
    public function show(SuratRujukan $rujukan): View
    {
        $this->authorize('view', $rujukan);

        $rujukan->load(['balita.orangTua', 'balita.pengukuranKms' => fn ($q) => $q->orderByDesc('date')->orderByDesc('id'), 'nakes']);

        return view('operasional.rujukan.show', compact('rujukan'));
    }

    /**
     * Form edit surat rujukan.
     */
    public function edit(SuratRujukan $rujukan): View
    {
        $this->authorize('update', $rujukan);

        $rujukan->load(['balita.orangTua', 'nakes']);

        return view('operasional.rujukan.edit', compact('rujukan'));
    }

    /**
     * Update data surat rujukan (tidak mengubah nomor surat).
     */
    public function update(UpdateSuratRujukanRequest $request, SuratRujukan $rujukan): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $rujukan);

        $updated = $this->suratRujukanService->updateSurat($rujukan, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Surat rujukan berhasil diperbarui.',
                'data' => $updated,
            ]);
        }

        return redirect()->route('rujukan.show', $updated)
            ->with('success', 'Data surat rujukan berhasil diperbarui.');
    }

    /**
     * Hapus surat rujukan.
     */
    public function destroy(Request $request, SuratRujukan $rujukan): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $rujukan);

        $this->suratRujukanService->deleteSurat($rujukan);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Surat rujukan berhasil dihapus.',
            ]);
        }

        return redirect()->route('rujukan.index')
            ->with('success', 'Surat rujukan berhasil dihapus.');
    }

    /**
     * Cetak atau unduh dokumen surat rujukan format PDF.
     */
    public function pdf(SuratRujukan $rujukan): Response
    {
        $this->authorize('view', $rujukan);

        $pdf = $this->suratRujukanService->generatePdf($rujukan);
        $fileName = 'Surat_Rujukan_' . str_replace('/', '_', $rujukan->nomor_surat) . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Verifikasi keaslian surat rujukan via QR (endpoint publik).
     */
    public function verify(string $code): View
    {
        $surat = $this->suratRujukanService->verifySurat($code);

        return view('operasional.rujukan.verify', compact('surat', 'code'));
    }
}
