<?php

namespace Database\Factories;

use App\Models\Balita;
use App\Models\SuratRujukan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SuratRujukan>
 */
class SuratRujukanFactory extends Factory
{
    protected $model = SuratRujukan::class;

    public function definition(): array
    {
        return [
            'balita_id' => Balita::factory(),
            'user_id' => User::factory(),
            'nomor_surat' => 'SR-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('####'),
            'faskes_tujuan' => 'RSUD dr. Soetomo',
            'status_follow_up' => 'belum',
            'alasan' => 'Indikasi gizi buruk berdasarkan pemantauan KMS berkala.',
            'qr_verification_code' => Str::uuid()->toString(),
        ];
    }

    public function proses(): static
    {
        return $this->state(fn () => [
            'status_follow_up' => 'proses',
        ]);
    }

    public function selesai(): static
    {
        return $this->state(fn () => [
            'status_follow_up' => 'selesai',
        ]);
    }
}
