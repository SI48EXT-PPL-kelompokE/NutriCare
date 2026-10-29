<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::all()->keyBy('name_role');
        
        // Hash password sekali untuk semua user (efisiensi)
        $password = Hash::make('password', ['rounds' => 10]);

        // 1 Admin
        User::create([
            'role_id' => $roles->get('Admin / Kepala Puskesmas')->id,
            'name' => 'Admin NutriCare',
            'username' => 'admin',
            'email' => 'admin@nutricare.test',
            'password' => $password,
            'nip' => '198501012010011001',
            'wilayah_tugas' => 'Puskesmas Kota',
            'active_status' => true,
            'last_login' => now()->subDays(1),
        ]);

        // 3 Nakes/Bidan
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'role_id' => $roles->get('Nakes / Bidan')->id,
                'name' => 'Bidan ' . $i,
                'username' => 'bidan' . $i,
                'email' => 'bidan' . $i . '@nutricare.test',
                'password' => $password,
                'nip' => '19900101201001100' . $i,
                'wilayah_tugas' => 'Wilayah ' . $i,
                'active_status' => $i == 2 ? false : true, // bidan2 nonaktif
                'last_login' => $i == 1 ? now()->subHours(5) : null,
            ]);
        }

        // 3 Kader
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'role_id' => $roles->get('Kader Posyandu')->id,
                'name' => 'Kader ' . $i,
                'username' => 'kader' . $i,
                'email' => 'kader' . $i . '@nutricare.test',
                'password' => $password,
                'nip' => null,
                'wilayah_tugas' => 'Posyandu ' . chr(64 + $i), // A, B, C
                'active_status' => true,
                'last_login' => $i == 3 ? now()->subMinutes(30) : null,
            ]);
        }

        $this->command->info('Users (admin, nakes, kader) berhasil di-seed: 7 akun');
        $this->command->info('10 akun warga akan dibuat oleh OrangTuaSeeder');
    }
}
