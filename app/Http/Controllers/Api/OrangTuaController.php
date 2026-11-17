<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrangTuaRequest;
use App\Http\Requests\UpdateOrangTuaRequest;
use App\Http\Resources\OrangTuaResource;
use App\Models\OrangTua;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OrangTuaController extends Controller
{
    /**
     * Menampilkan daftar Orang Tua (dengan fitur pencarian dan paginasi).
     */
    public function index(Request $request): JsonResponse
    {
        $query = OrangTua::with(['user.role'])
            ->withCount('balita');

        // Pencarian berdasarkan NIK atau Nama
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Filter status kehamilan jika disertakan
        if ($request->filled('pregnancy_status')) {
            $query->where('pregnancy_status', $request->query('pregnancy_status'));
        }

        // Filter status kepemilikan akun (has_account: true/false)
        if ($request->has('has_account')) {
            if ($request->boolean('has_account')) {
                $query->whereNotNull('user_id');
            } else {
                $query->whereNull('user_id');
            }
        }

        // Paginasi
        $perPage = (int) $request->input('per_page', 10);
        $perPage = ($perPage > 0 && $perPage <= 100) ? $perPage : 10;

        $orangTuaList = $query->latest()->paginate($perPage)->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Daftar data orang tua berhasil diambil.',
            'data' => OrangTuaResource::collection($orangTuaList),
            'meta' => [
                'current_page' => $orangTuaList->currentPage(),
                'from' => $orangTuaList->firstItem(),
                'last_page' => $orangTuaList->lastPage(),
                'per_page' => $orangTuaList->perPage(),
                'to' => $orangTuaList->lastItem(),
                'total' => $orangTuaList->total(),
            ],
        ]);
    }

    /**
     * Menyimpan data Orang Tua baru.
     * Mendukung opsi pembuatan akun User (Warga) dengan DB Transaction.
     */
    public function store(StoreOrangTuaRequest $request): JsonResponse
    {
        $orangTua = DB::transaction(function () use ($request) {
            $userId = null;

            // Jika create_account = true, buat akun User terlebih dahulu
            if ($request->boolean('create_account')) {
                $roleWarga = Role::where('name_role', 'Warga / Orang Tua')->first();

                if (!$roleWarga) {
                    $roleWarga = Role::firstOrCreate(['name_role' => 'Warga / Orang Tua']);
                }

                $user = User::create([
                    'role_id' => $roleWarga->id,
                    'name' => $request->name,
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'nip' => null,
                    'wilayah_tugas' => null,
                    'active_status' => true,
                ]);

                $userId = $user->id;
            }

            // Simpan data Orang Tua
            return OrangTua::create([
                'user_id' => $userId,
                'nik' => $request->nik,
                'name' => $request->name,
                'alamat' => $request->alamat,
                'kontak' => $request->kontak,
                'pregnancy_status' => $request->pregnancy_status,
            ]);
        });

        $orangTua->load(['user.role']);

        return response()->json([
            'success' => true,
            'message' => 'Data orang tua berhasil disimpan.',
            'data' => new OrangTuaResource($orangTua),
        ], 201);
    }

    /**
     * Menampilkan detail satu Orang Tua beserta relasi User dan Balita.
     */
    public function show(OrangTua $orangTua): JsonResponse
    {
        $orangTua->load(['user.role', 'balita']);

        return response()->json([
            'success' => true,
            'message' => 'Detail data orang tua berhasil diambil.',
            'data' => new OrangTuaResource($orangTua),
        ]);
    }

    /**
     * Memperbarui data Orang Tua dan sinkronisasi akun User (jika ada/dibuat).
     */
    public function update(UpdateOrangTuaRequest $request, OrangTua $orangTua): JsonResponse
    {
        DB::transaction(function () use ($request, $orangTua) {
            // Perbarui data entitas Orang Tua
            $orangTua->update([
                'nik' => $request->nik,
                'name' => $request->name,
                'alamat' => $request->alamat,
                'kontak' => $request->kontak,
                'pregnancy_status' => $request->pregnancy_status,
            ]);

            // Kondisi 1: Orang tua sudah terhubung dengan User -> perbarui info User
            if ($orangTua->user_id && $orangTua->user) {
                $userData = [
                    'name' => $request->name,
                ];

                if ($request->filled('username')) {
                    $userData['username'] = $request->username;
                }

                if ($request->filled('email')) {
                    $userData['email'] = $request->email;
                }

                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }

                $orangTua->user->update($userData);
            }
            // Kondisi 2: Orang tua sebelumnya tanpa akun, sekarang ingin membuat akun
            elseif (is_null($orangTua->user_id) && $request->boolean('create_account')) {
                $roleWarga = Role::where('name_role', 'Warga / Orang Tua')->first();

                if (!$roleWarga) {
                    $roleWarga = Role::firstOrCreate(['name_role' => 'Warga / Orang Tua']);
                }

                $user = User::create([
                    'role_id' => $roleWarga->id,
                    'name' => $request->name,
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'nip' => null,
                    'wilayah_tugas' => null,
                    'active_status' => true,
                ]);

                $orangTua->update(['user_id' => $user->id]);
            }
        });

        $orangTua->load(['user.role']);

        return response()->json([
            'success' => true,
            'message' => 'Data orang tua berhasil diperbarui.',
            'data' => new OrangTuaResource($orangTua),
        ]);
    }

    /**
     * Menghapus data Orang Tua.
     * Jika terhubung dengan User, akun User juga akan dibersihkan secara aman.
     */
    public function destroy(OrangTua $orangTua): JsonResponse
    {
        // Validasi relasi balita untuk integritas data
        if ($orangTua->balita()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Data orang tua tidak dapat dihapus karena masih memiliki data balita terdaftar.',
            ], 422);
        }

        // Validasi relasi aktivitas lainnya (reservasi, sesi konseling, pengaduan)
        if ($orangTua->reservasi()->exists() || $orangTua->sesiKonseling()->exists() || $orangTua->pengaduan()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Data orang tua tidak dapat dihapus karena masih memiliki riwayat aktivitas/reservasi/layanan posyandu.',
            ], 422);
        }

        DB::transaction(function () use ($orangTua) {
            $user = $orangTua->user;

            // Hapus record Orang Tua terlebih dahulu untuk melepaskan foreign key restrictOnDelete ke tabel users
            $orangTua->delete();

            // Hapus akun user terkait jika ada
            if ($user) {
                $user->delete();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Data orang tua dan akun terkait berhasil dihapus.',
        ]);
    }
}

