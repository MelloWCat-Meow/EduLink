<?php

namespace Tests\Feature\Ortu;

use App\Enums\StatusTutor;
use App\Enums\StatusVerifikasi;
use App\Models\DokumenVerifikasi;
use App\Models\MataPelajaran;
use App\Models\Tutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class CariTutorTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    /**
     * @param  array<string, mixed>  $profil
     */
    private function tutor(string $nama, array $profil = [], ?MataPelajaran $mapel = null): Tutor
    {
        $tutor = Tutor::factory()->siapDipesan($profil)->create(['nama' => $nama]);

        if ($mapel) {
            $tutor->mataPelajaran()->sync([$mapel->kode_mapel]);
        }

        return $tutor;
    }

    public function test_guests_can_search_and_only_listed_tutors_appear(): void
    {
        $this->tutor('Tutor Internal');
        Tutor::factory()->eksternal(StatusVerifikasi::Valid)->siapDipesan()->create(['nama' => 'Eksternal Lolos']);
        Tutor::factory()->eksternal(StatusVerifikasi::Menunggu)->siapDipesan()->create(['nama' => 'Eksternal Menunggu']);
        Tutor::factory()->eksternal(StatusVerifikasi::Ditolak)->siapDipesan()->create(['nama' => 'Eksternal Ditolak']);
        Tutor::factory()->siapDipesan()->create(['nama' => 'Belum Lengkap', 'status' => StatusTutor::Eksternal]);

        $this->get(route('tutor.index'))
            ->assertOk()
            ->assertSeeText('Tutor Internal')
            ->assertSeeText('Eksternal Lolos')
            ->assertDontSeeText('Eksternal Menunggu')
            ->assertDontSeeText('Eksternal Ditolak')
            ->assertDontSeeText('Belum Lengkap');
    }

    public function test_external_tutor_with_one_document_still_pending_is_hidden(): void
    {
        $tutor = Tutor::factory()->eksternal(StatusVerifikasi::Valid)->siapDipesan()->create(['nama' => 'Setengah Lolos']);
        DokumenVerifikasi::factory()->for($tutor, 'tutor')->create(['status_verifikasi' => StatusVerifikasi::Menunggu]);

        $this->get(route('tutor.index'))->assertDontSeeText('Setengah Lolos');
        $this->get(route('tutor.show', $tutor))->assertNotFound();
    }

    public function test_landing_parameters_filter_by_subject_level_and_district(): void
    {
        $fisikaSma = MataPelajaran::factory()->create(['kode_mapel' => 'FIS-SMA', 'nama' => 'Fisika', 'jenjang' => 'SMA']);
        $mtkSmp = MataPelajaran::factory()->create(['kode_mapel' => 'MTK-SMP', 'nama' => 'Matematika', 'jenjang' => 'SMP']);
        $this->tutor('Cocok Semua', ['lokasi' => 'Telanaipura'], $fisikaSma);
        $this->tutor('Beda Kecamatan', ['lokasi' => 'Jelutung'], $fisikaSma);
        $this->tutor('Beda Mapel', ['lokasi' => 'Telanaipura'], $mtkSmp);

        $this->get(route('tutor.index', ['mapel' => 'Fisika', 'jenjang' => 'sma', 'lokasi' => 'Telanaipura']))
            ->assertOk()
            ->assertSeeText('Cocok Semua')
            ->assertDontSeeText('Beda Kecamatan')
            ->assertDontSeeText('Beda Mapel')
            ->assertSee('<option value="Telanaipura" selected', false);
    }

    public function test_rate_range_filter(): void
    {
        $this->tutor('Murah', ['tarif' => '50000.00']);
        $this->tutor('Sedang', ['tarif' => '80000.00']);
        $this->tutor('Mahal', ['tarif' => '120000.00']);

        $this->get(route('tutor.index', ['tarif_min' => 60000, 'tarif_maks' => 100000]))
            ->assertSeeText('Sedang')
            ->assertDontSeeText('Murah')
            ->assertDontSeeText('Mahal');
    }

    public function test_minimum_rating_uses_only_reviews_that_are_shown(): void
    {
        $bagus = $this->tutor('Rating Bagus');
        $this->pesananSelesai(tutor: $bagus)->ulasan()->create(['nilai' => 5]);

        $disembunyikan = $this->tutor('Bagus Tapi Disembunyikan');
        $ulasan = $this->pesananSelesai(tutor: $disembunyikan)->ulasan()->create(['nilai' => 5]);
        $ulasan->forceFill(['ditampilkan' => false])->save();

        $this->tutor('Belum Diulas');

        $this->get(route('tutor.index', ['rating_min' => 4]))
            ->assertSeeText('Rating Bagus')
            ->assertDontSeeText('Bagus Tapi Disembunyikan')
            ->assertDontSeeText('Belum Diulas');
    }

    public function test_results_are_paginated(): void
    {
        Tutor::factory()->count(8)->siapDipesan()->create();

        $this->get(route('tutor.index'))->assertSeeText('8 tutor ditemukan')->assertSeeText('Halaman 1 dari 2');
    }

    public function test_profile_shows_free_slots_and_visible_reviews_only(): void
    {
        $tutor = $this->tutorSiapDipesan();
        $tutor->jadwal->first()->update(['hari' => 'senin']);
        $tutor->jadwal()->create(['hari' => 'sabtu', 'jam' => '08:00:00', 'status' => 'terisi']);
        $ulasan = $this->pesananSelesai(tutor: $tutor)->ulasan()->create(['nilai' => 2, 'komentar' => 'Komentar yang disembunyikan']);
        $ulasan->forceFill(['ditampilkan' => false])->save();

        $this->get(route('tutor.show', $tutor))
            ->assertOk()
            ->assertSeeText('Senin')
            ->assertDontSeeText('Sabtu')
            ->assertDontSeeText('Komentar yang disembunyikan')
            ->assertSeeText('Masuk untuk memesan');
    }
}
