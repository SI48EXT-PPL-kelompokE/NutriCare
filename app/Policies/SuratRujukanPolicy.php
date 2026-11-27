<?php

namespace App\Policies;

use App\Models\SuratRujukan;
use App\Models\User;

class SuratRujukanPolicy
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
     * Tentukan apakah user dapat melihat daftar surat rujukan.
     */
    public function viewAny(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // Nakes / Bidan diizinkan melihat rujukan
        if ($user->role?->name_role === 'Nakes / Bidan') {
            return true;
        }

        return $this->hasPermission($user, 'can_view');
    }

    /**
     * Tentukan apakah user dapat melihat detail surat rujukan.
     */
    public function view(?User $user, SuratRujukan $surat): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->role?->name_role === 'Nakes / Bidan') {
            return true;
        }

        return $this->hasPermission($user, 'can_view');
    }

    /**
     * Tentukan apakah user dapat membuat surat rujukan baru.
     */
    public function create(User $user): bool
    {
        if ($user->role?->name_role === 'Nakes / Bidan') {
            return true;
        }

        return $this->hasPermission($user, 'can_create');
    }

    /**
     * Tentukan apakah user dapat mengedit atau memperbarui surat rujukan.
     */
    public function update(User $user, SuratRujukan $surat): bool
    {
        if ($user->role?->name_role === 'Nakes / Bidan') {
            return true;
        }

        return $this->hasPermission($user, 'can_edit');
    }

    /**
     * Tentukan apakah user dapat menghapus surat rujukan.
     */
    public function delete(User $user, SuratRujukan $surat): bool
    {
        if ($user->role?->name_role === 'Nakes / Bidan') {
            return true;
        }

        return $this->hasPermission($user, 'can_delete');
    }

    /**
     * Helper periksa permission database.
     */
    protected function hasPermission(User $user, string $ability): bool
    {
        if (!$user->role) {
            return false;
        }

        return $user->role->permissions()
            ->where('module_name', 'rujukan')
            ->where($ability, true)
            ->exists();
    }
}
