<?php

namespace Database\Factories;

use App\Models\JadwalPosyandu;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JadwalPosyandu>
 */
class JadwalPosyanduFactory extends Factory
{
    protected $model = JadwalPosyandu::class;

    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 12);
        $hour = sprintf('%02d:00:00', $startHour);
        $jamSelesai = sprintf('%02d:00:00', $startHour + 2);

        return [
            'user_id' => User::factory(),
            'judul' => 'Posyandu Rutin ' . fake()->streetName(),
            'date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'hour' => $hour,
            'jam_selesai' => $jamSelesai,
            'location' => 'Balai RW ' . fake()->numberBetween(1, 10),
            'kuota_jadwal' => fake()->numberBetween(20, 50),
            'status' => 'aktif',
        ];
    }

    public function dibatalkan(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'dibatalkan',
        ]);
    }
}

