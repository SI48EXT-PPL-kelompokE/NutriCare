<?php

namespace Database\Factories;

use App\Models\PengukuranKms;
use App\Models\Balita;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PengukuranKms>
 */
class PengukuranKmsFactory extends Factory
{
    protected $model = PengukuranKms::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ambil balita acak atau buat baru
        $balita = Balita::inRandomOrder()->first() ?? Balita::factory()->create();

        // Usia balita maksimal 60 bulan
        $usiaBulanMax = min(60, (int) floor(max(0, $balita->birth_date->floatDiffInMonths(now()))));
        $usiaBulan = fake()->numberBetween(0, $usiaBulanMax);

        // Tanggal ukur = tanggal lahir + usia bulan + random days
        $dateUkur = $balita->birth_date->copy()->addMonths($usiaBulan)->addDays(fake()->numberBetween(0, 28));
        
        // Pastikan tidak melebihi hari ini
        if ($dateUkur->isAfter(now())) {
            $dateUkur = now()->subDays(fake()->numberBetween(1, 30));
        }

        // BB dan TB wajar menurut usia kasar (estimasi sederhana)
        // Usia 0-12 bulan: BB ~3-10 kg, TB ~50-75 cm
        // Usia 12-24 bulan: BB ~8-12 kg, TB ~75-85 cm
        // Usia 24-60 bulan: BB ~10-18 kg, TB ~85-110 cm
        if ($usiaBulan <= 12) {
            $beratBadan = fake()->randomFloat(1, 3.0, 10.0);
            $tinggiBadan = fake()->randomFloat(1, 50.0, 75.0);
        } elseif ($usiaBulan <= 24) {
            $beratBadan = fake()->randomFloat(1, 8.0, 12.0);
            $tinggiBadan = fake()->randomFloat(1, 75.0, 85.0);
        } else {
            $beratBadan = fake()->randomFloat(1, 10.0, 18.0);
            $tinggiBadan = fake()->randomFloat(1, 85.0, 110.0);
        }

        // Ambil petugas berrole nakes atau kader
        $petugas = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['Nakes / Bidan', 'Kader Posyandu']);
        })->inRandomOrder()->first() ?? User::factory()->create();

        return [
            'balita_id' => $balita->id,
            'user_id' => $petugas->id,
            'date' => $dateUkur,
            'berat_badan' => $beratBadan,
            'tinggi_badan' => $tinggiBadan,
            'lingkar_lengan_atas' => fake()->boolean(70) ? fake()->randomFloat(1, 10.0, 16.0) : null,
            // z-score dan status_gizi tetap null (KF-14/KF-15)
            'z_score_bbu' => null,
            'z_score_tbu' => null,
            'z_score_bbtb' => null,
            'status_gizi' => null,
        ];
    }
}
