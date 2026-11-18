<?php

namespace App\Policies;

use App\Models\JadwalPosyandu;
use App\Models\User;

class JadwalPosyanduPolicy
{
    /**
     * Buka akses penuh untuk Admin / Kepala Puskesmas.
     */
    public function before(?User $user, string $ability): ?bool
    {
        if ($user && $user->role?->name_role === 'Admin / Kepala Puskesmas') {
            return true;
        }

        return null;
    }

    /**
     * Tentukan apakah user dapat melihat daftar jadwal.
     */
    public function viewAny(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // Nakes, Kader, dan Warga diizinkan melihat jadwal posyandu
        $allowedRoles = ['Nakes / Bidan', 'Kader Posyandu', 'Warga / Orang Tua'];
        if (in_array($user->role?->name_role, $allowedRoles, true)) {
            return true;
        }

        return $this->hasPermission($user, 'can_view');
    }

    /**
     * Tentukan apakah user dapat melihat detail jadwal tertentu.
     */
    public function view(?User $user, JadwalPosyandu $jadwal): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Tentukan apakah user dapat membuat jadwal baru.
     */
    public function create(User $user): bool
    {
        if ($user->role?->name_role === 'Kader Posyandu') {
            return true;
        }

        return $this->hasPermission($user, 'can_create');
    }

    /**
     * Tentukan apakah user dapat mengubah jadwal.
     */
    public function update(User $user, JadwalPosyandu $jadwal): bool
    {
        if ($user->role?->name_role === 'Kader Posyandu') {
            return true;
        }

        return $this->hasPermission($user, 'can_edit');
    }

    /**
     * Tentukan apakah user dapat menghapus atau membatalkan jadwal.
     */
    public function delete(User $user, JadwalPosyandu $jadwal): bool
    {
        if ($user->role?->name_role === 'Kader Posyandu') {
            return true;
        }

        return $this->hasPermission($user, 'can_delete');
    }

    /**
     * Helper cek permission database matriks hak akses.
     */
    protected function hasPermission(User $user, string $ability): bool
    {
        if (!$user->role) {
            return false;
        }

        return $user->role->permissions()
            ->where('module_name', 'jadwal')
            ->where($ability, true)
            ->exists();
    }
}

