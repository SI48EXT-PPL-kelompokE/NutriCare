<?php

namespace Database\Factories;

use App\Models\OrangTua;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrangTua>
 */
class OrangTuaFactory extends Factory
{
    protected $model = OrangTua::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nik' => fake()->unique()->numerify('################'),
            'name' => fake()->name(),
            'alamat' => fake()->address(),
            'kontak' => fake()->numerify('08##########'),
            'pregnancy_status' => fake()->randomElement(['tidak_hamil', 'hamil']),
        ];
    }
}
