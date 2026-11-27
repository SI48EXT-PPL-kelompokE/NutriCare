<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckInQrRequest;
use App\Models\JadwalPosyandu;
use App\Models\Presensi;
use App\Services\PresensiService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PresensiController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected PresensiService $presensiService
    ) {}

    /**
     * Tampilkan halaman presensi dan rekap kehadiran hari ini.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Presensi::class);

        $jadwalList = JadwalPosyandu::aktif()->orderBy('date', 'desc')->get();

        // Default ke jadwal hari ini atau jadwal terdekat
        $selectedJadwalId = $request->filled('jadwal_id')
            ? (int) $request->jadwal_id
            : ($jadwalList->firstWhere('date', now()->toDateString())?->id ?? $jadwalList->first()?->id);

        $query = Presensi::with(['balita.orangTua', 'jadwal']);

        if ($selectedJadwalId) {
            $query->where('jadwal_id', $selectedJadwalId);
        }

        if ($request->filled('status')) {
            $query->where('presence_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->whereHas('balita', function ($bq) use ($search) {
                $bq->where('name', 'like', "%{$search}%")
                    ->orWhere('qr_code', 'like', "%{$search}%")
                    ->orWhereHas('orangTua', function ($oq) use ($search) {
                        $oq->where('name', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
            });
        }

        $presensiList = $query->latest()->paginate(15)->withQueryString();
        $rekap = $selectedJadwalId ? $this->presensiService->getRekapPresensi($selectedJadwalId) : null;
        $selectedJadwal = $selectedJadwalId ? JadwalPosyandu::find($selectedJadwalId) : null;

        if ($request->wantsJson()) {
            return response()->json([
                'presensi' => $presensiList,
                'rekap' => $rekap,
            ]);
        }

        return view('operasional.presensi.index', compact(
            'jadwalList',
            'selectedJadwalId',
            'selectedJadwal',
            'presensiList',
            'rekap'
        ));
    }

    /**
     * Check-in via pemindaian QR kartu balita (html5-qrcode).
     * Tidak menerima input manual identitas balita.
     */
    public function checkIn(CheckInQrRequest $request): JsonResponse
    {
        $this->authorize('create', Presensi::class);

        $result = $this->presensiService->checkInViaQr(
            $request->validated('jadwal_id'),
            $request->validated('qr_code')
        );

        $statusCode = $result['is_new'] ? 201 : 200;

        return response()->json($result, $statusCode);
    }

    /**
     * Koreksi status presensi balita.
     */
    public function update(Request $request, Presensi $presensi): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $presensi);

        $validated = $request->validate([
            'presence_status' => ['required', 'in:hadir,tidak_hadir'],
        ]);

        $updated = $this->presensiService->updateStatus($presensi, $validated['presence_status']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Status presensi berhasil diperbarui.',
                'data' => $updated,
            ]);
        }

        return redirect()->back()->with('success', 'Status presensi berhasil diperbarui.');
    }

    /**
     * Hapus data presensi.
     */
    public function destroy(Request $request, Presensi $presensi): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $presensi);

        $this->presensiService->deletePresensi($presensi);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Data presensi berhasil dihapus.',
            ]);
        }

        return redirect()->back()->with('success', 'Data presensi berhasil dihapus.');
    }
}
