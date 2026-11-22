<?php

namespace Database\Factories;

use App\Enums\Hari;
use App\Enums\StatusJadwal;
use App\Models\JadwalKetersediaan;
use App\Models\Tutor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JadwalKetersediaan>
 */
class JadwalKetersediaanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_tutor' => Tutor::factory(),
            'hari' => fake()->randomElement(Hari::cases()),
            'jam' => fake()->randomElement(['15:00:00', '16:00:00', '19:00:00']),
            'status' => StatusJadwal::Tersedia,
        ];
    }
}
