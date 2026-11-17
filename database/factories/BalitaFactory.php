<?php

namespace Database\Factories;

use App\Models\Balita;
use App\Models\OrangTua;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Balita>
 */
class BalitaFactory extends Factory
{
    protected $model = Balita::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $orangTua = OrangTua::inRandomOrder()->first() ?? OrangTua::factory()->create();

        return [
            'orang_tua_id' => $orangTua->id,
            'name' => fake()->firstName(),
            'birth_date' => fake()->dateTimeBetween('-5 years', '-1 month'),
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'qr_code' => fake()->unique()->uuid(),
        ];
    }
}
