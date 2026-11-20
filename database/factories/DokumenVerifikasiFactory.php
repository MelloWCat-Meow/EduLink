<?php

namespace Database\Factories;

use App\Enums\JenisDokumen;
use App\Enums\StatusVerifikasi;
use App\Models\DokumenVerifikasi;
use App\Models\Tutor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DokumenVerifikasi>
 */
class DokumenVerifikasiFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_tutor' => Tutor::factory()->state(['status' => 'eksternal']),
            'jenis' => fake()->randomElement(JenisDokumen::cases()),
            'berkas' => 'dokumen-contoh/berkas.pdf',
            'status_verifikasi' => StatusVerifikasi::Menunggu,
        ];
    }
}
