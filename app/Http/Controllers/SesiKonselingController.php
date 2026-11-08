<?php

namespace App\Http\Controllers;

use App\Models\OrangTua;
use App\Models\SesiKonseling;
use App\Models\User;
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
     * Menampilkan form pengajuan sesi konseling gizi baru.
     */
    public function create()
    {
        $orangTuaList = OrangTua::orderBy('name')->get();
        $topikList = SesiKonseling::TOPIK_LIST;

        return view('edukasi.konseling.create', compact('orangTuaList', 'topikList'));
    }

    /**
     * Menyimpan pengajuan sesi konseling baru dari orang tua.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'orang_tua_id' => 'required|exists:orang_tua,id',
            'topik' => 'required|string|max:255',
            'jadwal' => 'nullable|date',
        ]);

        $validated['status'] = 'diajukan';
        $validated['user_id'] = null;
        $validated['hasil'] = null;

        SesiKonseling::create($validated);

        return redirect()->route('konseling.index')
            ->with('success', 'Pengajuan sesi konseling gizi berhasil dikirim.');
    }

    /**
     * Menampilkan detail sesi konseling beserta riwayat dan tindakan nakes.
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

