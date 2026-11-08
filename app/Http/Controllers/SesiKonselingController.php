<?php

namespace App\Http\Controllers;

use App\Models\OrangTua;
use App\Models\SesiKonseling;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SesiKonselingController extends Controller
{
    /**
     * Menampilkan daftar sesi konseling gizi dengan filter status, topik, dan kata kunci.
     */
    public function index(Request $request)
    {
        $query = SesiKonseling::with(['orangTua', 'nakes'])
            ->status($request->query('status'))
            ->topik($request->query('topik'))
            ->search($request->query('q'));

        $sesiList = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $stats = [
            'total' => SesiKonseling::count(),
            'diajukan' => SesiKonseling::where('status', 'diajukan')->count(),
            'dijadwalkan' => SesiKonseling::where('status', 'dijadwalkan')->count(),
            'selesai' => SesiKonseling::where('status', 'selesai')->count(),
        ];

        $topikList = SesiKonseling::TOPIK_LIST;

        return view('edukasi.konseling.index', compact('sesiList', 'stats', 'topikList'));
    }

    /**
     * Menampilkan formulir pengajuan sesi konseling baru.
     */
    public function create()
    {
        $orangTuaList = OrangTua::with('balita')->orderBy('name')->get();
        $topikList = SesiKonseling::TOPIK_LIST;

        return view('edukasi.konseling.create', compact('orangTuaList', 'topikList'));
    }

    /**
     * Menyimpan pengajuan sesi konseling baru dengan status awal diajukan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'orang_tua_id' => 'required|exists:orang_tua,id',
            'topik' => 'required|string|max:255',
            'jadwal_harapan' => 'nullable|date',
            'keluhan' => 'nullable|string|max:1000',
        ]);

        $sesi = SesiKonseling::create([
            'orang_tua_id' => $validated['orang_tua_id'],
            'topik' => $validated['topik'],
            'jadwal' => $validated['jadwal_harapan'] ?? null,
            'status' => 'diajukan',
            'user_id' => null,
            'hasil' => !empty($validated['keluhan']) ? 'Keluhan awal: ' . $validated['keluhan'] : null,
        ]);

        return redirect()->route('konseling.index')
            ->with('success', 'Pengajuan sesi konseling gizi berhasil dikirim dan menunggu penjadwalan nakes.');
    }

    /**
     * Menampilkan detail sesi konseling dan formulir tindakan nakes.
     */
    public function show($id)
    {
        $sesi = SesiKonseling::with(['orangTua.balita', 'nakes'])->findOrFail($id);
        
        $nakesList = User::whereHas('role', function ($q) {
            $q->where('name_role', 'like', '%Nakes%')
              ->orWhere('name_role', 'like', '%Bidan%');
        })->where('active_status', true)->get();

        return view('edukasi.konseling.show', compact('sesi', 'nakesList'));
    }

    /**
     * Memeriksa ketersediaan jadwal nakes via JSON (SCRUM-177 [KF-48]).
     */
    public function cekKetersediaan(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'jadwal' => 'required|date',
            'ignore_id' => 'nullable|integer',
        ]);

        $nakes = User::find($request->user_id);
        if (!$nakes || !$nakes->active_status) {
            return response()->json([
                'available' => false,
                'message' => 'Tenaga kesehatan terpilih sedang tidak aktif bertugas.',
            ]);
        }

        $targetTime = Carbon::parse($request->jadwal);
        $startWindow = $targetTime->copy()->subMinutes(59);
        $endWindow = $targetTime->copy()->addMinutes(59);

        $conflict = SesiKonseling::where('user_id', $request->user_id)
            ->when($request->ignore_id, fn($q) => $q->where('id', '!=', $request->ignore_id))
            ->where('status', 'dijadwalkan')
            ->whereBetween('jadwal', [$startWindow, $endWindow])
            ->first();

        if ($conflict) {
            $jam = $conflict->jadwal ? $conflict->jadwal->format('H:i') : '';
            return response()->json([
                'available' => false,
                'message' => "Tenaga kesehatan {$nakes->name} telah memiliki agenda pada pukul {$jam} WIB. Nakes tidak tersedia, mohon pilih waktu lain.",
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => "Tenaga kesehatan {$nakes->name} tersedia pada waktu tersebut.",
        ]);
    }

    /**
     * Menetapkan jadwal konsultasi dan memvalidasi ketersediaan nakes (SCRUM-177 [KF-48]).
     */
    public function jadwalkan(Request $request, $id)
    {
        $sesi = SesiKonseling::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'jadwal' => 'required|date',
        ]);

        $nakes = User::findOrFail($validated['user_id']);
        if (!$nakes->active_status) {
            return back()->withInput()->withErrors([
                'user_id' => 'Tenaga kesehatan terpilih sedang tidak aktif bertugas.',
            ]);
        }

        // Pengecekan ketersediaan nakes: durasi konsultasi diasumsikan 1 jam (59 menit batas overlap)
        $targetTime = Carbon::parse($validated['jadwal']);
        $startWindow = $targetTime->copy()->subMinutes(59);
        $endWindow = $targetTime->copy()->addMinutes(59);

        $conflict = SesiKonseling::where('user_id', $validated['user_id'])
            ->where('id', '!=', $sesi->id)
            ->where('status', 'dijadwalkan')
            ->whereBetween('jadwal', [$startWindow, $endWindow])
            ->first();

        if ($conflict) {
            $jamBentrok = $conflict->jadwal ? $conflict->jadwal->format('H:i') : '';
            return back()->withInput()->withErrors([
                'jadwal' => "Tenaga kesehatan {$nakes->name} telah memiliki agenda konseling pada pukul {$jamBentrok} WIB. Nakes tidak tersedia: minta pilih waktu lain.",
            ]);
        }

        $sesi->update([
            'user_id' => $validated['user_id'],
            'jadwal' => $validated['jadwal'],
            'status' => 'dijadwalkan',
        ]);

        return redirect()->route('konseling.show', $sesi->id)
            ->with('success', 'Sesi konseling berhasil dijadwalkan bersama tenaga kesehatan.');
    }

    /**
     * Menyimpan hasil evaluasi konseling klinis dan memperbarui status menjadi selesai.
     */
    public function selesai(Request $request, $id)
    {
        $sesi = SesiKonseling::findOrFail($id);

        $validated = $request->validate([
            'hasil' => 'required|string|min:5',
        ]);

        $sesi->update([
            'hasil' => $validated['hasil'],
            'status' => 'selesai',
        ]);

        return redirect()->route('konseling.show', $sesi->id)
            ->with('success', 'Konsultasi selesai dan hasil konseling telah berhasil dicatat.');
    }

    /**
     * Menghapus rekaman sesi konseling.
     */
    public function destroy($id)
    {
        $sesi = SesiKonseling::findOrFail($id);
        $sesi->delete();

        return redirect()->route('konseling.index')
            ->with('success', 'Data sesi konseling berhasil dihapus.');
    }
}
