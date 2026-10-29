<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name_role' => 'Admin / Kepala Puskesmas'],
            ['name_role' => 'Nakes / Bidan'],
            ['name_role' => 'Kader Posyandu'],
            ['name_role' => 'Warga / Orang Tua'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }

        $this->command->info('Roles berhasil di-seed: 4 role');
    }
}
