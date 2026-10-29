<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PemberianVitamin;
use App\Models\Balita;
use App\Models\User;
use Carbon\Carbon;

class PemberianVitaminSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');
        
        $petugasIds = User::whereHas('role', function($q) {
            $q->whereIn('name_role', ['Nakes / Bidan', 'Kader Posyandu']);
        })->pluck('id')->toArray();

        $balitas = Balita::limit(30)->get(); // Hanya 30 balita untuk efisiensi
        $totalVitamin = 0;

        foreach ($balitas as $balita) {
            $umurBulan = Carbon::parse($balita->birth_date)->diffInMonths(now());

            // Vitamin A jika umur >= 6 bulan
            if ($umurBulan >= 6 && rand(1, 100) <= 70) {
                try {
                    PemberianVitamin::create([
                        'balita_id' => $balita->id,
                        'user_id' => $faker->randomElement($petugasIds),
                        'jenis_vit' => 'Vitamin A',
                        'dosis' => $umurBulan < 12 ? 'Kapsul Biru (100.000 IU)' : 'Kapsul Merah (200.000 IU)',
                        'date' => now()->subMonths(rand(1, 6)),
                        'periode' => now()->subMonths(rand(1, 6))->format('Y-m'),
                    ]);
                    $totalVitamin++;
                } catch (\Exception $e) {
                    // Skip duplikat
                }
            }
        }

        $this->command->info("PemberianVitamin berhasil di-seed: {$totalVitamin} pemberian");
    }
}
