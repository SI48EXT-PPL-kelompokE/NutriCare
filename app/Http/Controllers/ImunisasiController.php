<?php

namespace App\Http\Controllers;

use App\Models\Imunisasi;
use App\Models\Balita;
use App\Http\Requests\ImunisasiRequest;
use Illuminate\Http\Request;

class ImunisasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Imunisasi::with(['balita.orangTua', 'petugas'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        // Filter: pencarian
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('balita', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })
                ->orWhere('jenis_vaksin', 'like', "%{$search}%")
                ->orWhere('no_batch', 'like', "%{$search}%");
            });
        }

        // Filter: jenis vaksin
        if ($jenisVaksin = $request->input('jenis_vaksin')) {
            $query->where('jenis_vaksin', $jenisVaksin);
        }

        // Filter: rentang tanggal
        if ($tanggalDari = $request->input('tanggal_dari')) {
            $query->whereDate('tanggal', '>=', $tanggalDari);
        }
        if ($tanggalSampai = $request->input('tanggal_sampai')) {
            $query->whereDate('tanggal', '<=', $tanggalSampai);
        }

        // Filter: balita_id (untuk melihat riwayat satu balita dari halaman lain)
        if ($balitaId = $request->input('balita_id')) {
            $query->where('balita_id', $balitaId);
        }

        $imunisasi = $query->paginate(10)->withQueryString();

        return view('imunisasi.index', [
            'imunisasi' => $imunisasi,
            'jenisVaksinList' => config('imunisasi.jenis_vaksin'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Ambil daftar balita dengan data orang tua dan hitung usia dalam bulan
        $balita = Balita::with('orangTua')
            ->orderBy('name')
            ->get()
            ->map(function ($b) {
                $b->usia_bulan = $b->birth_date->diffInMonths(now());
                return $b;
            });

        $balitaIdTerpilih = $request->input('balita_id');

        return view('imunisasi.create', [
            'balita' => $balita,
            'balitaIdTerpilih' => $balitaIdTerpilih,
            'jenisVaksinList' => config('imunisasi.jenis_vaksin'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ImunisasiRequest $request)
    {
        $validated = $request->validated();
        
        // Isi petugas otomatis dari user yang login
        // TODO: hapus fallback setelah autentikasi (Adhani) tersedia
        $validated['user_id'] = auth()->id() ?? \App\Models\User::whereHas('role', function($q) {
            $q->where('name_role', 'Nakes / Bidan');
        })->first()->id;

        Imunisasi::create($validated);

        return redirect()
            ->route('imunisasi.index')
            ->with('success', 'Catatan imunisasi berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Imunisasi $imunisasi)
    {
        $imunisasi->load(['balita.orangTua', 'petugas']);
        
        // Hitung usia balita dalam bulan saat imunisasi
        $usiaBulan = $imunisasi->balita->birth_date->diffInMonths($imunisasi->tanggal);

        return view('imunisasi.show', [
            'imunisasi' => $imunisasi,
            'usiaBulan' => $usiaBulan,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Imunisasi $imunisasi)
    {
        $imunisasi->load(['balita.orangTua', 'petugas']);

        // Ambil daftar balita
        $balita = Balita::with('orangTua')
            ->orderBy('name')
            ->get()
            ->map(function ($b) {
                $b->usia_bulan = $b->birth_date->diffInMonths(now());
                return $b;
            });

        return view('imunisasi.edit', [
            'imunisasi' => $imunisasi,
            'balita' => $balita,
            'jenisVaksinList' => config('imunisasi.jenis_vaksin'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ImunisasiRequest $request, Imunisasi $imunisasi)
    {
        $validated = $request->validated();
        
        // Petugas asli tidak berubah (user_id tetap)
        unset($validated['user_id']);

        $imunisasi->update($validated);

        return redirect()
            ->route('imunisasi.index')
            ->with('success', 'Catatan imunisasi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Imunisasi $imunisasi)
    {
        $imunisasi->delete();

        return redirect()
            ->route('imunisasi.index')
            ->with('success', 'Catatan imunisasi berhasil dihapus.');
    }
}
