<?php

namespace App\Http\Controllers;

use App\Http\Requests\RescheduleReservasiRequest;
use App\Http\Requests\StoreReservasiRequest;
use App\Models\Balita;
use App\Models\JadwalPosyandu;
use App\Models\Reservasi;
use App\Services\ReservasiService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservasiController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ReservasiService $reservasiService
    ) {}

    /**
     * Tampilkan daftar reservasi (Warga melihat miliknya, Kader/Admin melihat semua).
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Reservasi::class);

        $user = $request->user();
        $isWarga = $user->role?->name_role === 'Warga / Orang Tua';

        $query = Reservasi::with(['jadwal', 'balita', 'orangTua']);

        if ($isWarga) {
            $orangTuaId = $user->orangTua?->id ?? 0;
            $query->where('orang_tua_id', $orangTuaId);
        }

        if ($request->filled('jadwal_id')) {
            $query->where('jadwal_id', (int) $request->jadwal_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->whereHas('balita', function ($bq) use ($search) {
                $bq->where('name', 'like', "%{$search}%")
                    ->orWhere('qr_code', 'like', "%{$search}%");
            });
        }

        $reservasiList = $query->latest()->paginate(15)->withQueryString();
        $jadwalList = JadwalPosyandu::aktif()->where('date', '>=', now()->subDays(7)->toDateString())->orderBy('date', 'desc')->get();
        $availableJadwal = JadwalPosyandu::aktif()->where('date', '>=', now()->toDateString())->orderBy('date', 'asc')->get();

        if ($request->wantsJson()) {
            return response()->json($reservasiList);
        }

        return view('operasional.reservasi.index', compact('reservasiList', 'jadwalList', 'availableJadwal', 'isWarga'));
    }

    /**
     * Form reservasi baru.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Reservasi::class);

        $user = $request->user();
        $isWarga = $user->role?->name_role === 'Warga / Orang Tua';

        $jadwalList = JadwalPosyandu::aktif()
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->get();

        if ($isWarga) {
            $balitaList = $user->orangTua?->balita ?? collect();
        } else {
            $balitaList = Balita::with('orangTua')->orderBy('name', 'asc')->get();
        }

        return view('operasional.reservasi.create', compact('jadwalList', 'balitaList', 'isWarga'));
    }

    /**
     * Simpan reservasi baru.
     */
    public function store(StoreReservasiRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', Reservasi::class);

        $reservasi = $this->reservasiService->createReservasi(
            $request->validated(),
            $request->user()
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Reservasi kunjungan posyandu berhasil dibuat.',
                'data' => $reservasi->load(['jadwal', 'balita']),
            ], 201);
        }

        return redirect()
            ->route('reservasi.index')
            ->with('success', 'Reservasi kunjungan berhasil dibuat.');
    }

    /**
     * Detail reservasi.
     */
    public function show(Reservasi $reservasi): View|JsonResponse
    {
        $this->authorize('view', $reservasi);

        $reservasi->load(['jadwal', 'balita', 'orangTua']);

        if (request()->wantsJson()) {
            return response()->json($reservasi);
        }

        $availableJadwal = JadwalPosyandu::aktif()
            ->where('date', '>=', now()->toDateString())
            ->where('id', '!=', $reservasi->jadwal_id)
            ->orderBy('date', 'asc')
            ->get();

        return view('operasional.reservasi.show', compact('reservasi', 'availableJadwal'));
    }

    /**
     * Reschedule reservasi ke jadwal posyandu lain.
     */
    public function reschedule(RescheduleReservasiRequest $request, Reservasi $reservasi): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $reservasi);

        $updated = $this->reservasiService->reschedule(
            $reservasi,
            $request->validated('new_jadwal_id')
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Jadwal reservasi berhasil diubah.',
                'data' => $updated,
            ]);
        }

        return redirect()
            ->route('reservasi.show', $reservasi)
            ->with('success', 'Jadwal kunjungan berhasil diubah ke ' . $updated->jadwal->judul . '.');
    }

    /**
     * Batalkan reservasi.
     */
    public function cancel(Request $request, Reservasi $reservasi): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $reservasi);

        $cancelled = $this->reservasiService->cancelReservasi($reservasi);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Reservasi kunjungan telah dibatalkan.',
                'data' => $cancelled,
            ]);
        }

        return redirect()
            ->route('reservasi.index')
            ->with('success', 'Reservasi kunjungan berhasil dibatalkan.');
    }

    /**
     * Hapus permanen / batalkan reservasi.
     */
    public function destroy(Request $request, Reservasi $reservasi): JsonResponse|RedirectResponse
    {
        return $this->cancel($request, $reservasi);
    }
}
