<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengukuranKms;
use App\Models\Balita;
use App\Models\User;
use App\Models\StandardLms;
use Carbon\Carbon;

class PengukuranKmsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');
        
        $petugasIds = User::whereHas('role', function($q) {
            $q->whereIn('name_role', ['Nakes / Bidan', 'Kader Posyandu']);
        })->pluck('id')->toArray();

        $balitas = Balita::all();
        $totalPengukuran = 0;

        foreach ($balitas as $balita) {
            $umurBulan = Carbon::parse($balita->birth_date)->diffInMonths(now());
            if ($umurBulan > 59) $umurBulan = 59; // Batas data LMS
            
            $jumlahPengukuran = rand(3, min(5, $umurBulan + 1)); // Maksimal 5 pengukuran
            
            for ($j = 0; $j < $jumlahPengukuran; $j++) {
                $umurSaatUkur = max(0, $umurBulan - $jumlahPengukuran + $j + 1);
                
                $tanggalUkur = Carbon::parse($balita->birth_date)->addMonths($umurSaatUkur);
                if ($tanggalUkur->isFuture()) continue;

                // Ambil data LMS
                $lmsData = StandardLms::where('indikator', 'BB_U')
                    ->where('jenis_kelamin', $balita->jenis_kelamin)
                    ->where('acuan_nilai', $umurSaatUkur)
                    ->first();

                if (!$lmsData) continue;

                // Z-score acak dengan distribusi realistis
                $zScore = $this->generateZScore($totalPengukuran);
                
                // Hitung berat badan dari LMS
                $l = (float) $lmsData->l;
                $m = (float) $lmsData->m;
                $s = (float) $lmsData->s;
                
                if (abs($l) > 0.01) {
                    $beratBadan = $m * pow((1 + $l * $s * $zScore), (1 / $l));
                } else {
                    $beratBadan = $m * exp($s * $zScore);
                }

                // Estimasi tinggi badan sederhana
                $tinggiBadan = 50 + ($umurSaatUkur * 1.5); // Pendekatan kasar
                
                // Z-score dummy untuk TB_U dan BB_TB
                $zScoreTbu = rand(-20, 20) / 10;
                $zScoreBbtb = rand(-20, 20) / 10;

                // Status gizi
                $statusGizi = 'Normal';
                if ($zScore < -3) $statusGizi = 'Gizi Buruk';
                elseif ($zScore < -2) $statusGizi = 'Gizi Kurang';
                elseif ($zScoreTbu < -2) $statusGizi = 'Stunting';
                elseif ($zScoreBbtb < -2) $statusGizi = 'Wasting';

                PengukuranKms::create([
                    'balita_id' => $balita->id,
                    'user_id' => $faker->randomElement($petugasIds),
                    'date' => $tanggalUkur,
                    'berat_badan' => round($beratBadan, 2),
                    'tinggi_badan' => round($tinggiBadan, 1),
                    'lingkar_lengan_atas' => rand(110, 160) / 10,
                    'z_score_bbu' => round($zScore, 2),
                    'z_score_tbu' => round($zScoreTbu, 2),
                    'z_score_bbtb' => round($zScoreBbtb, 2),
                    'status_gizi' => $statusGizi,
                ]);

                $totalPengukuran++;
            }
        }

        $this->command->info("PengukuranKms berhasil di-seed: {$totalPengukuran} pengukuran");
        $this->command->warn('CATATAN: z_score_tbu dan z_score_bbtb saat ini DUMMY (tabel LMS TB_U dan BB_TB belum tersedia)');
    }

    private function generateZScore($index): float
    {
        // Distribusi: beberapa yang pertama gizi buruk, lalu normal mayoritas
        if ($index < 5) return rand(-35, -30) / 10; // Gizi Buruk
        if ($index < 15) return rand(-30, -21) / 10; // Gizi Kurang/Stunting
        if (rand(1, 100) <= 75) return rand(-15, 15) / 10; // Normal
        return rand(-20, 20) / 10; // Variasi
    }
}
