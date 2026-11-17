<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBalitaRequest;
use App\Http\Requests\UpdateBalitaRequest;
use App\Http\Resources\BalitaResource;
use App\Models\Balita;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BalitaController extends Controller
{
    /**
     * Menampilkan daftar data Balita dengan dukungan paginasi, filter, dan pencarian.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Balita::with(['orangTua']);

        // Pencarian berdasarkan nama balita, kode QR, atau nama/NIK orang tua
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('qr_code', 'like', "%{$search}%")
                  ->orWhereHas('orangTua', function ($oq) use ($search) {
                      $oq->where('name', 'like', "%{$search}%")
                         ->orWhere('nik', 'like', "%{$search}%");
                  });
            });
        }

        // Filter berdasarkan orang_tua_id
        if ($request->filled('orang_tua_id')) {
            $query->where('orang_tua_id', (int) $request->query('orang_tua_id'));
        }

        // Filter berdasarkan jenis_kelamin (L / P)
        if ($request->filled('jenis_kelamin') && in_array($request->query('jenis_kelamin'), ['L', 'P'], true)) {
            $query->where('jenis_kelamin', $request->query('jenis_kelamin'));
        }

        // Paginasi
        $perPage = (int) $request->input('per_page', 10);
        $perPage = ($perPage > 0 && $perPage <= 100) ? $perPage : 10;

        $balitaList = $query->latest()->paginate($perPage)->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Daftar data balita berhasil diambil.',
            'data' => BalitaResource::collection($balitaList),
            'meta' => [
                'current_page' => $balitaList->currentPage(),
                'from' => $balitaList->firstItem(),
                'last_page' => $balitaList->lastPage(),
                'per_page' => $balitaList->perPage(),
                'to' => $balitaList->lastItem(),
                'total' => $balitaList->total(),
            ],
        ]);
    }

    /**
     * Menyimpan data Balita baru beserta identifier qr_code otomatis.
     */
    public function store(StoreBalitaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $balita = DB::transaction(function () use ($validated) {
            return Balita::create([
                'orang_tua_id' => $validated['orang_tua_id'],
                'name' => $validated['name'],
                'birth_date' => $validated['birth_date'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'qr_code' => $this->generateUniqueQrCode(),
            ]);
        });

        $balita->load(['orangTua']);

        return response()->json([
            'success' => true,
            'message' => 'Data balita berhasil ditambahkan.',
            'data' => new BalitaResource($balita),
        ], 201);
    }

    /**
     * Menampilkan detail satu Balita beserta informasi Orang Tua terkait.
     */
    public function show(Balita $balita): JsonResponse
    {
        $balita->load(['orangTua']);

        return response()->json([
            'success' => true,
            'message' => 'Detail data balita berhasil diambil.',
            'data' => new BalitaResource($balita),
        ]);
    }

    /**
     * Memperbarui data Balita yang diizinkan tanpa mengubah qr_code.
     */
    public function update(UpdateBalitaRequest $request, Balita $balita): JsonResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($balita, $validated) {
            $balita->update($validated);
        });

        $balita->refresh()->load(['orangTua']);

        return response()->json([
            'success' => true,
            'message' => 'Data balita berhasil diperbarui.',
            'data' => new BalitaResource($balita),
        ]);
    }

    /**
     * Menghapus data Balita jika belum memiliki riwayat layanan kesehatan/posyandu.
     */
    public function destroy(Balita $balita): JsonResponse
    {
        if ($this->hasServiceHistory($balita)) {
            return response()->json([
                'success' => false,
                'message' => 'Data balita tidak dapat dihapus karena masih digunakan pada riwayat layanan kesehatan atau posyandu.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($balita) {
                $balita->delete();
            });
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data balita tidak dapat dihapus karena masih terhubung dengan data layanan lain.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data balita berhasil dihapus.',
        ]);
    }

    /**
     * Memeriksa apakah balita memiliki riwayat pada salah satu dari 7 modul layanan.
     */
    private function hasServiceHistory(Balita $balita): bool
    {
        return $balita->pengukuranKms()->exists()
            || $balita->imunisasi()->exists()
            || $balita->pemberianVitamin()->exists()
            || $balita->distribusiPmt()->exists()
            || $balita->reservasi()->exists()
            || $balita->presensi()->exists()
            || $balita->suratRujukan()->exists();
    }

    /**
     * Menghasilkan identifier qr_code unik dengan pola NC-XXXXXXXXXX.
     */
    private function generateUniqueQrCode(): string
    {
        do {
            $qrCode = 'NC-' . strtoupper(Str::random(10));
        } while (Balita::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }
}

