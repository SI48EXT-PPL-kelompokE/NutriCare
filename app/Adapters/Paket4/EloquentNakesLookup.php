<?php

namespace App\Adapters\Paket4;

use App\Contracts\Paket4\NakesLookup;
use App\Models\User;
use Illuminate\Support\Collection;

class EloquentNakesLookup implements NakesLookup
{
    public function findActiveNakes(int $id): ?User
    {
        return User::where('id', $id)
            ->where('active_status', true)
            ->whereHas('role', function ($query) {
                $query->where('name_role', 'Nakes / Bidan');
            })
            ->first();
    }

    public function getActiveNakesList(): Collection
    {
        return User::where('active_status', true)
            ->whereHas('role', function ($query) {
                $query->where('name_role', 'Nakes / Bidan');
            })
            ->orderBy('name', 'asc')
            ->get();
    }
}

