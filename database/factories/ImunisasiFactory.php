<?php

namespace Database\Factories;

use App\Models\Balita;
use App\Models\User;
use App\Models\Imunisasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Imunisasi>
 */
class ImunisasiFactory extends Factory
{
    protected $model = Imunisasi::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $balita = Balita::inRandomOrder()->first() ?? Balita::factory()->create();
        
        // Tanggal imunisasi: antara tanggal lahir balita dan hari ini
        $tanggalLahir = $balita->birth_date;
        $tanggalImunisasi = fake()->dateTimeBetween($tanggalLahir, 'now');

        // Petugas: user dengan role nakes atau kader
        $petugas = User::whereHas('role', function($q) {
            $q->whereIn('name_role', ['Nakes / Bidan', 'Kader Posyandu']);
        })->inRandomOrder()->first() ?? User::factory()->create();

        return [
            'balita_id' => $balita->id,
            'user_id' => $petugas->id,
            'jenis_vaksin' => fake()->randomElement(config('imunisasi.jenis_vaksin')),
            'tanggal' => $tanggalImunisasi,
            'no_batch' => fake()->optional(0.7)->regexify('BTC-[0-9]{8}'),
        ];
    }
}
