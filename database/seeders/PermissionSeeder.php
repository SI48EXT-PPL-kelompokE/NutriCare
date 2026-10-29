<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::all()->keyBy('name_role');
        
        // Matriks hak akses: [module_name => [role_name => [view, create, edit, delete]]]
        $permissions = [
            // Admin / Kepala Puskesmas
            'akun' => ['Admin / Kepala Puskesmas' => [1,1,1,1]],
            'role' => ['Admin / Kepala Puskesmas' => [1,1,1,1]],
            'hak_akses' => ['Admin / Kepala Puskesmas' => [1,1,1,1]],
            'paket_pmt' => ['Admin / Kepala Puskesmas' => [1,1,1,1], 'Kader Posyandu' => [1,0,0,0]],
            'stok_gudang' => ['Admin / Kepala Puskesmas' => [1,1,1,1], 'Kader Posyandu' => [1,1,1,0]],
            'artikel' => ['Admin / Kepala Puskesmas' => [1,1,1,1], 'Nakes / Bidan' => [1,1,1,0], 'Kader Posyandu' => [1,1,1,0], 'Warga / Orang Tua' => [1,0,0,0]],
            'pengaduan' => ['Admin / Kepala Puskesmas' => [1,0,1,0], 'Warga / Orang Tua' => [0,1,0,0]],
            'dashboard' => ['Admin / Kepala Puskesmas' => [1,0,0,0]],
            'laporan' => ['Admin / Kepala Puskesmas' => [1,1,0,0]],
            
            // Nakes / Bidan
            'orang_tua' => ['Nakes / Bidan' => [1,1,1,0], 'Kader Posyandu' => [1,1,1,0]],
            'balita' => ['Nakes / Bidan' => [1,1,1,0], 'Kader Posyandu' => [1,1,1,0]],
            'kms' => ['Nakes / Bidan' => [1,1,1,0], 'Kader Posyandu' => [1,1,1,0], 'Warga / Orang Tua' => [1,0,0,0]],
            'imunisasi' => ['Nakes / Bidan' => [1,1,1,0], 'Kader Posyandu' => [1,1,0,0]],
            'vitamin' => ['Nakes / Bidan' => [1,1,1,0], 'Kader Posyandu' => [1,1,0,0]],
            'konseling' => ['Nakes / Bidan' => [1,0,1,0], 'Warga / Orang Tua' => [0,1,0,0]],
            'rujukan' => ['Nakes / Bidan' => [1,1,1,0]],
            
            // Kader Posyandu
            'distribusi_pmt' => ['Kader Posyandu' => [1,1,1,0]],
            'jadwal' => ['Kader Posyandu' => [1,1,1,0], 'Warga / Orang Tua' => [1,0,0,0]],
            'presensi' => ['Kader Posyandu' => [1,1,0,0]],
            
            // Warga / Orang Tua
            'reservasi' => ['Warga / Orang Tua' => [1,1,1,0]],
        ];

        foreach ($permissions as $moduleName => $rolePerms) {
            foreach ($rolePerms as $roleName => $perms) {
                $role = $roles->get($roleName);
                if (!$role) continue;
                
                Permission::create([
                    'role_id' => $role->id,
                    'name' => ucfirst($moduleName),
                    'module_name' => $moduleName,
                    'can_view' => $perms[0],
                    'can_create' => $perms[1],
                    'can_edit' => $perms[2],
                    'can_delete' => $perms[3],
                ]);
            }
        }

        $this->command->info('Permissions berhasil di-seed');
    }
}
