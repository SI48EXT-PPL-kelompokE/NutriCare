<?php

namespace App\Policies;

use App\Models\Balita;
use App\Models\Role;
use App\Models\User;

/**
 * Otorisasi akses grafik pertumbuhan balita (KF-16).
 *
 * CATATAN: middleware/permission penuh buatan Adhani belum tersedia untuk
 * halaman ini, sehingga policy sederhana ini dipakai dulu. Selaraskan kembali
 * setelah matriks hak akses lengkap tersedia.
 */
class BalitaPolicy
{
    /**
     * Admin (Kepala Puskesmas) selalu boleh.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->active_status && $user->role?->name_role === Role::ADMIN) {
            return true;
        }

        return null;
    }

    /**
     * Boleh melihat grafik pertumbuhan balita.
     */
    public function lihatPertumbuhan(User $user, Balita $balita): bool
    {
        if (! $user->active_status) {
            return false;
        }

        $role = $user->role?->name_role;

        // Nakes/Bidan & Kader Posyandu: seluruh balita (hak akses modul kms:view).
        if (in_array($role, ['Nakes / Bidan', 'Kader Posyandu'], true)) {
            return true;
        }

        // Warga: hanya balita milik orang tua dengan user_id sama dengan akun ini.
        if ($role === Role::RESIDENT) {
            $userPemilik = $balita->orangTua?->user_id;

            return $userPemilik !== null && (int) $userPemilik === (int) $user->id;
        }

        return false;
    }
}
