<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\OrangTua;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class OrangTuaSeeder extends Seeder
{
    public function run(): void
    {
        $roleWarga = Role::where('name_role', 'Warga / Orang Tua')->first();
        $password = Hash::make('password', ['rounds' => 10]);
        
        $faker = \Faker\Factory::create('id_ID');

        // 10 orang tua dengan akun warga
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'role_id' => $roleWarga->id,
                'name' => $faker->name,
                'username' => 'warga' . $i,
                'email' => 'warga' . $i . '@nutricare.test',
                'password' => $password,
                'nip' => null,
                'wilayah_tugas' => null,
                'active_status' => true,
                'last_login' => $i <= 3 ? now()->subDays(rand(1, 7)) : null,
            ]);

            OrangTua::create([
                'user_id' => $user->id,
                'nik' => $this->generateNik($faker),
                'name' => $user->name,
                'alamat' => $faker->address,
                'kontak' => $faker->phoneNumber,
                'pregnancy_status' => $i <= 2 ? 'hamil' : 'tidak_hamil',
            ]);
        }

        // 20 orang tua tanpa akun (hanya dicatat kader)
        for ($i = 11; $i <= 30; $i++) {
            OrangTua::create([
                'user_id' => null,
                'nik' => $this->generateNik($faker),
                'name' => $faker->name,
                'alamat' => $faker->address,
                'kontak' => $faker->phoneNumber,
                'pregnancy_status' => $i <= 13 ? 'hamil' : 'tidak_hamil',
            ]);
        }

        $this->command->info('OrangTua berhasil di-seed: 30 orang tua (10 dengan akun, 20 tanpa akun)');
    }

    private function generateNik($faker): string
    {
        // NIK 16 digit: DDMMYY + kode wilayah (6) + nomor urut (4)
        return sprintf(
            '%02d%02d%02d%06d%04d',
            rand(1, 31), // tanggal
            rand(1, 12), // bulan
            rand(70, 95), // tahun kelahiran (1970-1995)
            rand(100000, 999999), // kode wilayah
            rand(1, 9999) // nomor urut
        );
    }
}
