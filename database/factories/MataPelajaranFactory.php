<?php

namespace Database\Factories;

use App\Models\MataPelajaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MataPelajaran>
 */
class MataPelajaranFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jenjang = fake()->randomElement(['SD', 'SMP', 'SMA']);

        return [
            'kode_mapel' => fake()->unique()->bothify('M??#-').$jenjang,
            'nama' => fake()->unique()->word(),
            'jenjang' => $jenjang,
        ];
    }
}
