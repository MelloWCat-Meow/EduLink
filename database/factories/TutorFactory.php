<?php

namespace Database\Factories;

use App\Enums\Peran;
use App\Enums\StatusTutor;
use App\Enums\StatusVerifikasi;
use App\Enums\StatusVerifikasiTutor;
use App\Models\AkunPengguna;
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
            'status_verifikasi' => StatusVerifikasiTutor::Terverifikasi,
            'kontak' => fake()->unique()->safeEmail(),
            'no_hp' => '08'.fake()->numerify('##########'),
        ];
    }

    public function draf(): static
    {
        return $this->state(['status_verifikasi' => StatusVerifikasiTutor::Draf]);
    }

    public function menunggu(): static
    {
        return $this->state(fn (): array => ['status_verifikasi' => StatusVerifikasiTutor::Menunggu, 'diajukan_pada' => now()->subDay()]);
    }

    /**
     * @param  array<string, string|null>  $bagian  section => admin note
     */
    public function perluPerbaikan(array $bagian = ['profil' => 'Lengkapi pengalaman mengajar.'], string $catatan = 'Ada yang perlu diperbaiki.'): static
    {
        return $this->state(['status_verifikasi' => StatusVerifikasiTutor::PerluPerbaikan, 'bagian_perbaikan' => $bagian, 'catatan_verifikasi' => $catatan]);
    }

    public function ditolak(string $catatan = 'Kompetensi belum sesuai.'): static
    {
        return $this->state(['status_verifikasi' => StatusVerifikasiTutor::Ditolak, 'catatan_verifikasi' => $catatan]);
    }

    /**
     * A sign-in account with the tutor role, using the tutor's email.
     */
    public function denganAkun(): static
    {
        return $this->state(fn (array $attributes): array => [
            'id_akun' => AkunPengguna::factory()->state(['peran' => Peran::Tutor, 'kontak' => $attributes['kontak'], 'nama' => $attributes['nama']]),
        ]);
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
