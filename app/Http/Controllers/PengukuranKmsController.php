<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengukuranKmsRequest;
use App\Models\PengukuranKms;
use App\Models\Balita;
use App\Services\ZScoreService;
use App\Services\StatusGiziService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        if ($status = $request->input('status')) {
            if ($status === '__null') {
                $query->whereNull('status_gizi');
            } elseif (\App\Enums\StatusGizi::tryFrom($status)) {
                $query->status($status);
            }
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

        // KF-14: Hitung z-score otomatis
        try {
            $pengukuran = new PengukuranKms($data);
            $pengukuran->balita_id = $data['balita_id'];
            $pengukuran->date = $data['date'];
            
            // Load relasi balita untuk perhitungan
            $pengukuran->load('balita');
            
            $zScoreService = app(ZScoreService::class);
            $zScores = $zScoreService->untukPengukuran($pengukuran);
            
            $data['z_score_bbu'] = $zScores['z_score_bbu'];
            $data['z_score_tbu'] = $zScores['z_score_tbu'];
            $data['z_score_bbtb'] = $zScores['z_score_bbtb'];
        } catch (\Exception $e) {
            // Jangan gagalkan penyimpanan, cukup log error
            Log::warning('Gagal menghitung z-score saat store pengukuran', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            
            $data['z_score_bbu'] = null;
            $data['z_score_tbu'] = null;
            $data['z_score_bbtb'] = null;
        }

        // KF-15: tentukan status gizi dari z-score tersimpan
        try {
            $pengukuranSementara = new PengukuranKms($data);
            $pengukuranSementara->z_score_bbu = $data['z_score_bbu'];
            $pengukuranSementara->z_score_tbu = $data['z_score_tbu'];
            $pengukuranSementara->z_score_bbtb = $data['z_score_bbtb'];
            $data['status_gizi'] = StatusGiziService::untukPengukuran($pengukuranSementara)['status']?->value;
        } catch (\Throwable $e) {
            $data['status_gizi'] = null;
            Log::warning('Gagal menentukan status gizi saat store pengukuran', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }

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

        // KF-14: Hitung ulang z-score (BB/TB/tanggal bisa berubah)
        try {
            // Update data dulu untuk perhitungan
            $kms->fill($data);
            
            // Pastikan relasi balita dimuat
            if (!$kms->relationLoaded('balita')) {
                $kms->load('balita');
            }
            
            $zScoreService = app(ZScoreService::class);
            $zScores = $zScoreService->untukPengukuran($kms);
            
            $data['z_score_bbu'] = $zScores['z_score_bbu'];
            $data['z_score_tbu'] = $zScores['z_score_tbu'];
            $data['z_score_bbtb'] = $zScores['z_score_bbtb'];
        } catch (\Exception $e) {
            // Jangan gagalkan penyimpanan, cukup log error
            Log::warning('Gagal menghitung z-score saat update pengukuran', [
                'error' => $e->getMessage(),
                'pengukuran_id' => $kms->id,
                'data' => $data,
            ]);
            
            $data['z_score_bbu'] = null;
            $data['z_score_tbu'] = null;
            $data['z_score_bbtb'] = null;
        }

        // KF-15: tentukan status gizi dari z-score hasil perhitungan ulang
        try {
            $kms->z_score_bbu = $data['z_score_bbu'];
            $kms->z_score_tbu = $data['z_score_tbu'];
            $kms->z_score_bbtb = $data['z_score_bbtb'];
            $hasil = StatusGiziService::untukPengukuran($kms);
            $data['status_gizi'] = $hasil['status']?->value;
        } catch (\Throwable $e) {
            $data['status_gizi'] = null;
            Log::warning('Gagal menentukan status gizi saat update pengukuran', [
                'error' => $e->getMessage(),
                'pengukuran_id' => $kms->id,
                'data' => $data,
            ]);
        }

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
