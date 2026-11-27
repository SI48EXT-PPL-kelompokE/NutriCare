<?php

namespace Database\Factories;

use App\Models\Balita;
use App\Models\JadwalPosyandu;
use App\Models\OrangTua;
use App\Models\Reservasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservasi>
 */
class ReservasiFactory extends Factory
{
    protected $model = Reservasi::class;

    public function definition(): array
    {
        $jadwal = JadwalPosyandu::factory();
        $orangTua = OrangTua::factory();

        return [
            'jadwal_id' => $jadwal,
            'orang_tua_id' => $orangTua,
            'balita_id' => fn (array $attributes) => Balita::factory()->create(['orang_tua_id' => $attributes['orang_tua_id']])->id,
            'status' => 'dikonfirmasi',
            'batas_reschedule' => now()->addDays(2),
        ];
    }

    public function dibatalkan(): static
    {
        return $this->state(fn () => [
            'status' => 'dibatalkan',
        ]);
    }

    public function menunggu(): static
    {
        return $this->state(fn () => [
            'status' => 'menunggu',
        ]);
    }

    public function selesai(): static
    {
        return $this->state(fn () => [
            'status' => 'selesai',
        ]);
    }

    public function expiredReschedule(): static
    {
        return $this->state(fn () => [
            'batas_reschedule' => now()->subHour(),
        ]);
    }
}
