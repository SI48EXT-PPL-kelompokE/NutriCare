<?php

namespace App\Policies;

use App\Models\Presensi;
use App\Models\Role;
use App\Models\User;

class PresensiPolicy
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
     * Tentukan apakah user dapat melihat daftar/rekap presensi.
     */
    public function viewAny(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $allowedRoles = ['Kader Posyandu', 'Nakes / Bidan', 'Admin / Kepala Puskesmas'];
        if (in_array($user->role?->name_role, $allowedRoles, true)) {
            return true;
        }

        return $this->hasPermission($user, 'can_view');
    }

    public function view(?User $user, Presensi $presensi): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Check-in presensi (hanya Kader Posyandu & Admin).
     */
    public function create(User $user): bool
    {
        if ($user->role?->name_role === 'Kader Posyandu') {
            return true;
        }

        return $this->hasPermission($user, 'can_create');
    }

    /**
     * Koreksi data/status presensi.
     */
    public function update(User $user, Presensi $presensi): bool
    {
        if ($user->role?->name_role === 'Kader Posyandu') {
            return true;
        }

        return $this->hasPermission($user, 'can_edit');
    }

    /**
     * Hapus catatan presensi.
     */
    public function delete(User $user, Presensi $presensi): bool
    {
        if ($user->role?->name_role === 'Kader Posyandu') {
            return true;
        }

        return $this->hasPermission($user, 'can_delete');
    }

    protected function hasPermission(User $user, string $ability): bool
    {
        if (!$user->role) {
            return false;
        }

        return $user->role->permissions()
            ->where('module_name', 'presensi')
            ->where($ability, true)
            ->exists();
    }
}
