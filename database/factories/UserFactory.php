<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ambil role acak atau default ke role pertama
        $role = \App\Models\Role::inRandomOrder()->first() ?? \App\Models\Role::first();

        return [
            'role_id' => $role?->id ?? 1,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'username' => fake()->unique()->userName(),
            'nip' => fake()->optional()->numerify('##########'),
            'wilayah_tugas' => fake()->city(),
            'active_status' => true,
        ];
    }

    /**
     * State user nakes.
     */
    public function nakes(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::where('name_role', 'Nakes / Bidan')->value('id') ?? 2,
        ]);
    }

    /**
     * State user kader.
     */
    public function kader(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::where('name_role', 'Kader Posyandu')->value('id') ?? 3,
        ]);
    }

    /**
     * State user admin.
     */
    public function admin(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::where('name_role', 'Admin / Kepala Puskesmas')->value('id') ?? 1,
        ]);
    }

    /**
     * State user nonaktif.
     */
    public function nonaktif(): static
    {
        return $this->state(fn () => [
            'active_status' => false,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'active_status' => false,
        ]);
    }
}
