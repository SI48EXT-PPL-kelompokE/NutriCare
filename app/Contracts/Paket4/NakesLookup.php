<?php

namespace App\Contracts\Paket4;

use App\Models\User;
use Illuminate\Support\Collection;

interface NakesLookup
{
    /**
     * Cari Nakes aktif berdasarkan ID.
     */
    public function findActiveNakes(int $id): ?User;

    /**
     * Dapatkan daftar seluruh Nakes aktif.
     * @return Collection<int, User>
     */
    public function getActiveNakesList(): Collection;
}

