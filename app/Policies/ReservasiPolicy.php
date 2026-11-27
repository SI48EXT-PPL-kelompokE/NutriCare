<?php

namespace App\Policies;

use App\Models\Reservasi;
use App\Models\User;

class ReservasiPolicy
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

    public function viewAny(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return true;
    }

    public function view(User $user, Reservasi $reservasi): bool
    {
        if (in_array($user->role?->name_role, ['Kader Posyandu', 'Nakes / Bidan'], true)) {
            return true;
        }

        return $reservasi->orang_tua_id === $user->orangTua?->id;
    }

    public function create(User $user): bool
    {
        if ($user->role?->name_role === 'Kader Posyandu') {
            return true;
        }

        if ($user->role?->name_role === 'Warga / Orang Tua') {
            return $user->orangTua !== null;
        }

        return $this->hasPermission($user, 'can_create');
    }

    public function update(User $user, Reservasi $reservasi): bool
    {
        if ($user->role?->name_role === 'Kader Posyandu') {
            return true;
        }

        if ($user->role?->name_role === 'Warga / Orang Tua') {
            return $reservasi->orang_tua_id === $user->orangTua?->id;
        }

        return $this->hasPermission($user, 'can_edit');
    }

    public function delete(User $user, Reservasi $reservasi): bool
    {
        if ($user->role?->name_role === 'Kader Posyandu') {
            return true;
        }

        if ($user->role?->name_role === 'Warga / Orang Tua') {
            return $reservasi->orang_tua_id === $user->orangTua?->id;
        }

        return $this->hasPermission($user, 'can_delete');
    }

    protected function hasPermission(User $user, string $ability): bool
    {
        if (!$user->role) {
            return false;
        }

        return $user->role->permissions()
            ->where('module_name', 'reservasi')
            ->where($ability, true)
            ->exists();
    }
}
