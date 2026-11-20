<?php

namespace Database\Factories;

use App\Enums\StatusTutor;
use App\Enums\StatusVerifikasi;
use App\Models\DokumenVerifikasi;
use App\Models\JadwalKetersediaan;
use App\Models\MataPelajaran;
use App\Models\ProfilTutor;
use App\Models\Tutor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tutor>
 */
class TutorFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->firstName().' '.fake()->lastName(),
            'status' => StatusTutor::Internal,
            'kontak' => '08'.fake()->numerify('##########'),
        ];
    }

    public function internal(): static
    {
        return $this->state(['status' => StatusTutor::Internal]);
    }

    /**
     * An external tutor with one document in the given verification status.
     */
    public function eksternal(StatusVerifikasi $statusDokumen = StatusVerifikasi::Valid): static
    {
        return $this->state(['status' => StatusTutor::Eksternal])
            ->has(DokumenVerifikasi::factory()->state(['status_verifikasi' => $statusDokumen]), 'dokumen');
    }

    /**
     * A tutor a parent can book: profile, one subject and one free weekly slot.
     */
    public function siapDipesan(array $profil = []): static
    {
        return $this->has(ProfilTutor::factory()->state($profil), 'profil')
            ->has(MataPelajaran::factory(), 'mataPelajaran')
            ->has(JadwalKetersediaan::factory(), 'jadwal');
    }
}
