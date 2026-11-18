<?php

namespace App\Http\Controllers;

use App\Contracts\Paket4\NakesLookup;
use App\Http\Requests\StoreJadwalPosyanduRequest;
use App\Http\Requests\UpdateJadwalPosyanduRequest;
use App\Models\JadwalPosyandu;
use App\Services\JadwalPosyanduService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalPosyanduController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        protected JadwalPosyanduService $jadwalService,
        protected NakesLookup $nakesLookup
    ) {}

    /**
     * Tampilkan halaman kalender/daftar jadwal atau return JSON event feed.
     */
    public function index(Request $request): View|JsonResponse
    {
        // 1. Endpoint JSON Event Feed untuk FullCalendar
        if ($request->wantsJson() || $request->has('feed')) {
            $validated = $request->validate([
                'start' => ['required', 'string'],
                'end' => ['required', 'string'],
                'nakes_id' => ['nullable', 'integer'],
            ]);

            $start = Carbon::parse($validated['start'])->format('Y-m-d');
            $end = Carbon::parse($validated['end'])->format('Y-m-d');

            // Batasi rentang request maksimal 90 hari
            if (Carbon::parse($start)->diffInDays(Carbon::parse($end)) > 90) {
                return response()->json([
                    'message' => 'Rentang feed kalender maksimal 90 hari.',
                ], 422);
            }

            $nakesId = !empty($validated['nakes_id']) ? (int) $validated['nakes_id'] : null;
            $feed = $this->jadwalService->getCalendarFeed($start, $end, $nakesId);

            return response()->json($feed);
        }

        // 2. View halaman jadwal
        $nakesList = $this->nakesLookup->getActiveNakesList();
        $jadwalList = JadwalPosyandu::with('penanggungJawab')
            ->orderBy('date', 'desc')
            ->orderBy('hour', 'asc')
            ->paginate(15);

        return view('operasional.jadwal.index', compact('nakesList', 'jadwalList'));
    }

    /**
     * Form tambah jadwal posyandu.
     */
    public function create(): View
    {
        $this->authorize('create', JadwalPosyandu::class);

        $nakesList = $this->nakesLookup->getActiveNakesList();
        return view('operasional.jadwal.create', compact('nakesList'));
    }

    /**
     * Simpan jadwal posyandu baru.
     */
    public function store(StoreJadwalPosyanduRequest $request): JsonResponse|RedirectResponse
    {
        $jadwal = $this->jadwalService->createJadwal($request->validated());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Jadwal posyandu berhasil dibuat.',
                'data' => $jadwal,
            ], 201);
        }

        return redirect()
            ->route('jadwal.index')
            ->with('success', "Jadwal '{$jadwal->judul}' berhasil dibuat.");
    }

    /**
     * Tampilkan detail jadwal posyandu.
     */
    public function show(JadwalPosyandu $jadwal): View|JsonResponse
    {
        $this->authorize('view', $jadwal);

        $jadwal->load(['penanggungJawab', 'reservasi.balita', 'presensi.balita']);

        if (request()->wantsJson()) {
            return response()->json($jadwal);
        }

        return view('operasional.jadwal.show', compact('jadwal'));
    }

    /**
     * Form ubah jadwal posyandu.
     */
    public function edit(JadwalPosyandu $jadwal): View
    {
        $this->authorize('update', $jadwal);

        $nakesList = $this->nakesLookup->getActiveNakesList();
        return view('operasional.jadwal.edit', compact('jadwal', 'nakesList'));
    }

    /**
     * Update data jadwal posyandu.
     */
    public function update(UpdateJadwalPosyanduRequest $request, JadwalPosyandu $jadwal): JsonResponse|RedirectResponse
    {
        $updated = $this->jadwalService->updateJadwal($jadwal, $request->validated());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Jadwal posyandu berhasil diperbarui.',
                'data' => $updated,
            ]);
        }

        return redirect()
            ->route('jadwal.index')
            ->with('success', "Jadwal '{$updated->judul}' berhasil diperbarui.");
    }

    /**
     * Batalkan jadwal (soft status).
     */
    public function cancel(Request $request, JadwalPosyandu $jadwal): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $jadwal);

        $cancelled = $this->jadwalService->cancelJadwal($jadwal);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Jadwal posyandu berhasil dibatalkan.',
                'data' => $cancelled,
            ]);
        }

        return redirect()
            ->route('jadwal.index')
            ->with('success', "Jadwal '{$cancelled->judul}' telah dibatalkan.");
    }

    /**
     * Hapus permanen jadwal kosong.
     */
    public function destroy(Request $request, JadwalPosyandu $jadwal): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $jadwal);

        $judul = $jadwal->judul;
        $this->jadwalService->deleteJadwal($jadwal);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Jadwal posyandu berhasil dihapus.',
            ]);
        }

        return redirect()
            ->route('jadwal.index')
            ->with('success', "Jadwal '{$judul}' berhasil dihapus.");
    }
}

