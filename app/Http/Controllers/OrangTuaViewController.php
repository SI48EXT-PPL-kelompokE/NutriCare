<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\OrangTua;
use Illuminate\Http\Request;

class OrangTuaViewController extends Controller
{
    /**
     * Menampilkan halaman Data Orang Tua / Ibu Hamil (Web UI sesuai referensi).
     */
    public function index(Request $request)
    {
        $query = OrangTua::with(['user', 'balita']);

        // Pencarian Nama atau NIK
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Filter Status (Semua Status, Hamil, Tidak Hamil / Kepala Keluarga)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('pregnancy_status', $request->status);
        }

        // Perhitungan Statistik Metrik
        $totalIbuHamil = OrangTua::where('pregnancy_status', 'hamil')->count();
        $totalKepalaKeluarga = OrangTua::where('pregnancy_status', 'tidak_hamil')->count();
        $totalOrangTua = OrangTua::count();
        $totalBalita = Balita::count();

        // Paginasi
        $orangTuas = $query->latest()->paginate(7)->withQueryString();

        return view('kependudukan.orang-tua.index', [
            'orangTuas' => $orangTuas,
            'totalIbuHamil' => $totalIbuHamil,
            'totalKepalaKeluarga' => $totalKepalaKeluarga,
            'totalOrangTua' => $totalOrangTua,
            'totalBalita' => $totalBalita,
        ]);
    }
}

