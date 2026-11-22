<?php

namespace Database\Factories;

use App\Models\Balita;
use App\Models\JadwalPosyandu;
use App\Models\Presensi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Presensi>
 */
class PresensiFactory extends Factory
{
    protected $model = Presensi::class;

    public function definition(): array
    {
        return [
            'jadwal_id' => JadwalPosyandu::factory(),
            'balita_id' => Balita::factory(),
            'presence_status' => 'hadir',
        ];
    }

    public function tidakHadir(): static
    {
        return $this->state(fn () => [
            'presence_status' => 'tidak_hadir',
        ]);
    }
}
