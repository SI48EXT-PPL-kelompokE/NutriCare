<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Balita;
use App\Models\OrangTua;
use Illuminate\Support\Str;

class BalitaSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');
        $orangTuas = OrangTua::all();

        $balitaCount = 0;
        
        foreach ($orangTuas as $orangTua) {
            // Setiap orang tua punya 1-2 balita
            $jumlahBalita = rand(1, 2);
            
            // Batasi total balita maksimal 45
            if ($balitaCount >= 45) break;
            
            for ($i = 0; $i < $jumlahBalita && $balitaCount < 45; $i++) {
                $umurBulan = rand(0, 59); // 0-59 bulan
                $birthDate = now()->subMonths($umurBulan)->subDays(rand(0, 28));
                
                Balita::create([
                    'orang_tua_id' => $orangTua->id,
                    'name' => $faker->firstName,
                    'birth_date' => $birthDate,
                    'jenis_kelamin' => $faker->randomElement(['L', 'P']),
                    'qr_code' => 'NC-' . strtoupper(Str::random(10)),
                ]);
                
                $balitaCount++;
            }
        }

        $this->command->info("Balita berhasil di-seed: {$balitaCount} balita");
    }
}
