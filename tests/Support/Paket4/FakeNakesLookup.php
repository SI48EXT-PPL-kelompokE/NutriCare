<?php

namespace Tests\Support\Paket4;

use App\Contracts\Paket4\NakesLookup;
use App\Models\User;
use Illuminate\Support\Collection;

class FakeNakesLookup implements NakesLookup
{
    /**
     * @var array<int, array{id: int, name: string, active_status: bool, role: string}>
     */
    protected array $nakesList = [];

    public function __construct(array $initialData = [])
    {
        if (empty($initialData)) {
            $this->nakesList = [
                101 => ['id' => 101, 'name' => 'Bidan Siti Aminah, S.Tr.Keb', 'active_status' => true, 'role' => 'Nakes / Bidan'],
                102 => ['id' => 102, 'name' => 'Bidan Ratna Dewi, A.Md.Keb', 'active_status' => true, 'role' => 'Nakes / Bidan'],
                103 => ['id' => 103, 'name' => 'Bidan Nonaktif Test', 'active_status' => false, 'role' => 'Nakes / Bidan'],
            ];
        } else {
            $this->nakesList = $initialData;
        }
    }

    public function findActiveNakes(int $id): ?User
    {
        if (isset($this->nakesList[$id]) && $this->nakesList[$id]['active_status']) {
            $user = new User();
            $user->id = $this->nakesList[$id]['id'];
            $user->name = $this->nakesList[$id]['name'];
            $user->active_status = true;
            return $user;
        }

        return null;
    }

    public function getActiveNakesList(): Collection
    {
        return collect($this->nakesList)
            ->filter(fn ($item) => $item['active_status'])
            ->map(function ($item) {
                $user = new User();
                $user->id = $item['id'];
                $user->name = $item['name'];
                $user->active_status = true;
                return $user;
            })
            ->values();
    }
}

