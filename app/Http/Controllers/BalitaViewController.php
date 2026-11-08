<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\OrangTua;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BalitaViewController extends Controller
{
    /**
     * Menampilkan halaman Daftar Data Balita (Web UI sesuai referensi).
     */
    public function index(Request $request): View
    {
        $query = Balita::with(['orangTua']);

        // Pencarian berdasarkan nama balita, kode QR, atau nama/NIK orang tua
        if ($request->filled('search')) {
            $search = trim((string) $request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('qr_code', 'like', "%{$search}%")
                  ->orWhereHas('orangTua', function ($oq) use ($search) {
                      $oq->where('name', 'like', "%{$search}%")
                         ->orWhere('nik', 'like', "%{$search}%");
                  });
            });
        }

        // Filter Jenis Kelamin (L / P)
        if ($request->filled('jenis_kelamin') && in_array($request->get('jenis_kelamin'), ['L', 'P'], true)) {
            $query->where('jenis_kelamin', $request->get('jenis_kelamin'));
        }

        // Filter Orang Tua
        if ($request->filled('orang_tua_id') && $request->get('orang_tua_id') !== 'all') {
            $query->where('orang_tua_id', (int) $request->get('orang_tua_id'));
        }

        $totalBalita = Balita::count();
        $totalOrangTua = OrangTua::count();
        $orangTuaOptions = OrangTua::orderBy('name')->get(['id', 'name', 'nik']);

        $balitas = $query->latest()->paginate(7)->withQueryString();

        return view('kependudukan.balita.index', [
            'balitas' => $balitas,
            'orangTuaOptions' => $orangTuaOptions,
            'totalBalita' => $totalBalita,
            'totalOrangTua' => $totalOrangTua,
        ]);
    }

    /**
     * Menampilkan halaman Detail Data Balita.
     */
    public function show(Balita $balita): View
    {
        $balita->load(['orangTua']);
        $orangTuaOptions = OrangTua::orderBy('name')->get(['id', 'name', 'nik']);

        return view('kependudukan.balita.show', [
            'balita' => $balita,
            'orangTuaOptions' => $orangTuaOptions,
        ]);
    }
}

