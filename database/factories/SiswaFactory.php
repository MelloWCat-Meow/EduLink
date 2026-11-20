<?php

namespace Database\Factories;

use App\Models\AkunPengguna;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Siswa>
 */
class SiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_akun' => AkunPengguna::factory()->ortu(),
            'nama' => fake()->firstName().' '.fake()->lastName(),
            'jenjang' => fake()->randomElement(['SD', 'SMP', 'SMA']),
            'kontak_wali' => fn (array $attributes): string => AkunPengguna::query()->findOrFail($attributes['id_akun'])->kontak,
        ];
    }
}
