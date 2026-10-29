<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Imunisasi;
use App\Models\Balita;
use App\Models\User;
use Carbon\Carbon;

class ImunisasiSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');
        
        $petugasIds = User::whereHas('role', function($q) {
            $q->whereIn('name_role', ['Nakes / Bidan', 'Kader Posyandu']);
        })->pluck('id')->toArray();

        $balitas = Balita::all();
        $totalImunisasi = 0;

        foreach ($balitas as $balita) {
            $umurBulan = Carbon::parse($balita->birth_date)->diffInMonths(now());
            
            // Tambahkan 2-4 imunisasi per balita saja
            $jumlahImun = rand(2, min(4, $umurBulan + 1));
            $vaksinList = ['HB-0', 'BCG', 'DPT-HB-Hib 1', 'DPT-HB-Hib 2', 'Campak/MR'];
            
            for ($i = 0; $i < $jumlahImun; $i++) {
                $tanggal = Carbon::parse($balita->birth_date)->addMonths(rand(0, $umurBulan));
                
                if (!$tanggal->isFuture()) {
                    Imunisasi::create([
                        'balita_id' => $balita->id,
                        'user_id' => $faker->randomElement($petugasIds),
                        'jenis_vaksin' => $vaksinList[$i % count($vaksinList)],
                        'tanggal' => $tanggal,
                        'no_batch' => 'BATCH-' . strtoupper($faker->bothify('??###')),
                    ]);
                    $totalImunisasi++;
                }
            }
        }

        $this->command->info("Imunisasi berhasil di-seed: {$totalImunisasi} imunisasi");
    }
}
