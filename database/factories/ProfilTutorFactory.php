<?php

namespace Database\Factories;

use App\Models\ProfilTutor;
use App\Models\Tutor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfilTutor>
 */
class ProfilTutorFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_tutor' => Tutor::factory(),
            'kompetensi' => fake()->sentence(),
            'pengalaman' => fake()->sentence(),
            'tarif' => '75000.00',
            'lokasi' => fake()->randomElement(config('landing.locations')),
            'melayani_online' => true,
            'melayani_kunjungan' => true,
        ];
    }
}
