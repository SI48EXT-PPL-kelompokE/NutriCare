<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengukuranKmsRequest;
use App\Models\PengukuranKms;
use App\Models\Balita;
use Illuminate\Http\Request;

class PengukuranKmsController extends Controller
{
    /**
     * Tampilkan daftar pengukuran (terbaru dulu)
     */
    public function index(Request $request)
    {
        $query = PengukuranKms::with(['balita.orangTua', 'petugas'])
            ->orderByDesc('date')
            ->orderByDesc('id');

        // Pencarian: nama balita atau nama orang tua
        if ($search = $request->input('search')) {
            $query->whereHas('balita', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('orangTua', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter balita tertentu (untuk riwayat satu balita)
        if ($balitaId = $request->input('balita_id')) {
            $query->where('balita_id', $balitaId);
        }

        // Filter rentang tanggal
        if ($startDate = $request->input('start_date')) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate = $request->input('end_date')) {
            $query->where('date', '<=', $endDate);
        }

        $pengukurans = $query->paginate(10)->withQueryString();

        return view('kms.index', compact('pengukurans'));
    }

    /**
     * Tampilkan form tambah pengukuran
     */
    public function create(Request $request)
    {
        $balitas = Balita::with('orangTua')
            ->orderBy('name')
            ->get();

        // Pre-select balita jika ada query ?balita_id=
        $selectedBalitaId = $request->input('balita_id');

        return view('kms.create', compact('balitas', 'selectedBalitaId'));
    }

    /**
     * Simpan pengukuran baru
     */
    public function store(PengukuranKmsRequest $request)
    {
        $data = $request->validated();
        
        // Isi user_id otomatis dari user yang login
        // TODO: hapus fallback setelah autentikasi (Adhani) tersedia
        $data['user_id'] = auth()->id() ?? \App\Models\User::whereHas('role', function ($q) {
            $q->whereIn('name', ['Nakes / Bidan', 'Kader Posyandu']);
        })->first()->id;

        // KF-14/KF-15: hitung z-score dan status gizi saat simpan
        // (belum diimplementasikan, z-score dan status_gizi tetap null)

        PengukuranKms::create($data);

        return redirect()
            ->route('kms.index')
            ->with('success', 'Data pengukuran berhasil disimpan.');
    }

    /**
     * Tampilkan detail satu pengukuran + riwayat balita
     */
    public function show(PengukuranKms $kms)
    {
        $kms->load(['balita.orangTua', 'petugas']);

        // Riwayat pengukuran balita ini (urutkan terbaru dulu)
        $riwayat = PengukuranKms::where('balita_id', $kms->balita_id)
            ->with('petugas')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        // Hitung selisih dari pengukuran sebelumnya
        $riwayatDenganSelisih = $riwayat->map(function ($item, $index) use ($riwayat) {
            $sebelumnya = $riwayat->get($index + 1);
            
            return [
                'pengukuran' => $item,
                'selisih_bb' => $sebelumnya ? $item->berat_badan - $sebelumnya->berat_badan : null,
                'selisih_tb' => $sebelumnya ? $item->tinggi_badan - $sebelumnya->tinggi_badan : null,
            ];
        });

        return view('kms.show', compact('kms', 'riwayatDenganSelisih'));
    }

    /**
     * Tampilkan form edit pengukuran
     */
    public function edit(PengukuranKms $kms)
    {
        $balitas = Balita::with('orangTua')
            ->orderBy('name')
            ->get();

        return view('kms.edit', compact('kms', 'balitas'));
    }

    /**
     * Update pengukuran
     */
    public function update(PengukuranKmsRequest $request, PengukuranKms $kms)
    {
        $data = $request->validated();
        
        // Jangan ubah user_id (petugas asli tetap)
        unset($data['user_id']);

        // KF-14/KF-15: hitung z-score dan status gizi saat simpan
        // (belum diimplementasikan, z-score dan status_gizi tetap null)

        $kms->update($data);

        return redirect()
            ->route('kms.show', $kms)
            ->with('success', 'Data pengukuran berhasil diperbarui.');
    }

    /**
     * Hapus pengukuran (hard delete)
     */
    public function destroy(PengukuranKms $kms)
    {
        $kms->delete();

        return redirect()
            ->route('kms.index')
            ->with('success', 'Data pengukuran berhasil dihapus.');
    }
}
