<?php

namespace Tests\Feature\Tutor;

use App\Enums\LangkahPendaftaran;
use App\Enums\Peran;
use App\Enums\StatusTutor;
use App\Enums\StatusVerifikasiTutor;
use App\Models\AkunPengguna;
use App\Models\MataPelajaran;
use App\Models\Tutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\MembuatPendaftar;
use Tests\TestCase;

class PendaftaranEksternalTest extends TestCase
{
    use MembuatPendaftar, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
    }

    /**
     * @return array<string, string>
     */
    private function isianAkun(): array
    {
        return [
            'nama' => 'Galih Prakoso',
            'kontak' => 'Galih@Contoh.com',
            'no_hp' => '0812-3456 7890',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ];
    }

    public function test_external_tutor_signs_up_fills_every_step_and_sends_the_application(): void
    {
        $this->post(route('tutor.register.store'), $this->isianAkun())
            ->assertRedirect(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Profil));

        $tutor = Tutor::query()->sole();
        $this->assertAuthenticatedAs($tutor->akun);
        $this->assertSame(Peran::Tutor, $tutor->akun->peran);
        $this->assertSame(StatusTutor::Eksternal, $tutor->status);
        $this->assertSame(StatusVerifikasiTutor::Draf, $tutor->status_verifikasi);
        $this->assertSame('galih@contoh.com', $tutor->kontak);
        $this->assertSame('081234567890', $tutor->no_hp);

        $this->put(route('tutor.pendaftaran.profil'), $this->isianProfil(['foto' => UploadedFile::fake()->image('foto.jpg')]))
            ->assertRedirect(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Dokumen));
        $this->post(route('tutor.pendaftaran.dokumen.store'), ['jenis' => 'ijazah', 'berkas' => $this->pdf()])->assertSessionHasNoErrors();
        $this->post(route('tutor.pendaftaran.dokumen.store'), ['jenis' => 'ktp', 'berkas' => UploadedFile::fake()->image('ktp.png')])->assertSessionHasNoErrors();
        $this->put(route('tutor.pendaftaran.jadwal'), ['slot' => [['hari' => 'senin', 'jam' => '16:00'], ['hari' => '', 'jam' => '']]])
            ->assertRedirect(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Kirim));

        $this->get(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Kirim))
            ->assertOk()
            ->assertSeeText('Semua bagian sudah lengkap.')
            ->assertSeeText('Rp75.000');

        $this->post(route('tutor.pendaftaran.kirim'))->assertRedirect(route('tutor.status'));

        $tutor->refresh();
        $this->assertSame(StatusVerifikasiTutor::Menunggu, $tutor->status_verifikasi);
        $this->assertNotNull($tutor->diajukan_pada);
        $this->assertSame('75000.00', $tutor->profil->tarif);
        $this->assertSame(['MTK-SMP'], $tutor->mataPelajaran->pluck('kode_mapel')->all());
        $this->assertDatabaseHas('riwayat_verifikasi_tutor', ['id_tutor' => $tutor->id_tutor, 'status' => 'menunggu', 'id_akun' => $tutor->id_akun]);
        Storage::disk('public')->assertExists($tutor->profil->foto);
        $this->assertFalse($tutor->isListed());
    }

    public function test_application_cannot_be_changed_while_waiting(): void
    {
        $tutor = $this->pendaftarLengkap();
        $tutor->forceFill(['status_verifikasi' => StatusVerifikasiTutor::Menunggu])->save();

        $this->actingAs($tutor->akun)->put(route('tutor.pendaftaran.profil'), $this->isianProfil(['tarif' => '99000']))
            ->assertRedirect(route('tutor.status'));
        $this->actingAs($tutor->akun)->get(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Profil))
            ->assertRedirect(route('tutor.status'));

        $this->assertSame('75000.00', $tutor->profil->refresh()->tarif);
    }

    public function test_each_step_is_kept_as_a_draft_and_sign_in_resumes_at_the_first_missing_step(): void
    {
        $this->post(route('tutor.register.store'), $this->isianAkun());
        $this->put(route('tutor.pendaftaran.profil'), $this->isianProfil());
        $this->post(route('logout'));

        $this->post(route('login.store'), ['kontak' => 'galih@contoh.com', 'password' => 'rahasia123'])
            ->assertRedirect(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Dokumen));

        $tutor = Tutor::query()->with('profil')->sole();
        $this->assertSame(StatusVerifikasiTutor::Draf, $tutor->status_verifikasi);
        $this->assertSame('Telanaipura', $tutor->profil->lokasi);

        $this->get(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Profil))
            ->assertOk()
            ->assertSee('value="Universitas Jambi"', false)
            ->assertSee('<option value="Telanaipura" selected', false);
    }

    public function test_sending_an_incomplete_application_is_refused_with_links_to_the_missing_steps(): void
    {
        $this->post(route('tutor.register.store'), $this->isianAkun());
        $this->put(route('tutor.pendaftaran.profil'), $this->isianProfil());

        $this->post(route('tutor.pendaftaran.kirim'))
            ->assertRedirect(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Kirim))
            ->assertSessionHas('galat');

        $this->assertSame(StatusVerifikasiTutor::Draf, Tutor::query()->sole()->status_verifikasi);

        $this->get(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Kirim))
            ->assertSeeText('Pendaftaran belum bisa dikirim.')
            ->assertSee(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Dokumen), false)
            ->assertSeeText('Ijazah wajib diunggah.')
            ->assertSeeText('KTP wajib diunggah.')
            ->assertSeeText('Tambahkan minimal satu jadwal mingguan.');
    }

    #[TestWith(['tarif', 'Tarif per jam belum diisi.'])]
    #[TestWith(['lokasi', 'Kecamatan layanan belum dipilih.'])]
    public function test_application_with_an_empty_rate_or_district_cannot_be_sent(string $kolom, string $pesan): void
    {
        $tutor = $this->pendaftarLengkap([$kolom => null]);

        $this->actingAs($tutor->akun)->post(route('tutor.pendaftaran.kirim'))
            ->assertRedirect(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Kirim));

        $this->assertSame(StatusVerifikasiTutor::Draf, $tutor->refresh()->status_verifikasi);
        $this->actingAs($tutor->akun)->get(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Kirim))->assertSeeText($pesan);
    }

    public function test_document_upload_requires_a_file(): void
    {
        $tutor = $this->pendaftarLengkap(denganDokumen: false);

        $this->actingAs($tutor->akun)->post(route('tutor.pendaftaran.dokumen.store'), ['jenis' => 'ijazah'])
            ->assertSessionHasErrors(['berkas' => 'Pilih berkas yang akan diunggah.']);
    }

    public function test_document_type_is_checked_from_the_content_not_the_name(): void
    {
        $tutor = $this->pendaftarLengkap(denganDokumen: false);
        $palsu = tempnam(sys_get_temp_dir(), 'palsu');
        file_put_contents($palsu, '<?php echo "bukan pdf";');

        $this->actingAs($tutor->akun)
            ->post(route('tutor.pendaftaran.dokumen.store'), ['jenis' => 'ijazah', 'berkas' => new UploadedFile($palsu, 'ijazah.pdf', 'application/pdf', null, true)])
            ->assertSessionHasErrors('berkas');

        $this->actingAs($tutor->akun)
            ->post(route('tutor.pendaftaran.dokumen.store'), ['jenis' => 'ijazah', 'berkas' => UploadedFile::fake()->create('ijazah.docx', 50, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')])
            ->assertSessionHasErrors('berkas');

        $this->assertSame(0, $tutor->dokumen()->count());
    }

    public function test_document_larger_than_two_megabytes_is_refused(): void
    {
        $tutor = $this->pendaftarLengkap(denganDokumen: false);

        $this->actingAs($tutor->akun)
            ->post(route('tutor.pendaftaran.dokumen.store'), ['jenis' => 'ktp', 'berkas' => UploadedFile::fake()->create('ktp.pdf', 2049, 'application/pdf')])
            ->assertSessionHasErrors(['berkas' => 'Ukuran berkas maksimal 2 MB.']);
    }

    public function test_documents_go_to_the_private_disk_under_a_random_name_and_replace_the_old_one(): void
    {
        $tutor = $this->pendaftarLengkap(denganDokumen: false);

        $this->actingAs($tutor->akun)->post(route('tutor.pendaftaran.dokumen.store'), ['jenis' => 'ktp', 'berkas' => $this->pdf('ktp-saya.pdf')]);
        $lama = $tutor->dokumen()->sole();

        Storage::disk('local')->assertExists($lama->berkas);
        $this->assertStringNotContainsString('ktp-saya', $lama->berkas);
        $this->assertSame([], Storage::disk('public')->allFiles());

        $this->actingAs($tutor->akun)->post(route('tutor.pendaftaran.dokumen.store'), ['jenis' => 'ktp', 'berkas' => $this->pdf('ktp-baru.pdf')]);

        $baru = $tutor->dokumen()->sole();
        $this->assertNotSame($lama->berkas, $baru->berkas);
        Storage::disk('local')->assertExists($baru->berkas);
        Storage::disk('local')->assertMissing($lama->berkas);
    }

    public function test_subjects_must_match_the_chosen_levels_and_the_rate_stays_in_range(): void
    {
        MataPelajaran::factory()->create(['kode_mapel' => 'FIS-SMA', 'nama' => 'Fisika', 'jenjang' => 'SMA']);
        $tutor = $this->pendaftarLengkap();

        $this->actingAs($tutor->akun)->put(route('tutor.pendaftaran.profil'), $this->isianProfil(['jenjang' => ['SMP'], 'mapel' => ['MTK-SMP', 'FIS-SMA']]))
            ->assertSessionHasErrors('mapel');

        $this->actingAs($tutor->akun)->put(route('tutor.pendaftaran.profil'), $this->isianProfil(['tarif' => '10000']))
            ->assertSessionHasErrors(['tarif' => 'Tarif per jam antara Rp25.000 dan Rp500.000.']);

        $this->actingAs($tutor->akun)->put(route('tutor.pendaftaran.profil'), $this->isianProfil(['layanan' => []]))
            ->assertSessionHasErrors('layanan');
    }

    public function test_sign_up_refuses_an_email_that_belongs_to_an_internal_tutor_record(): void
    {
        Tutor::factory()->internal()->draf()->create(['kontak' => 'lestari@contoh.com']);

        $this->post(route('tutor.register.store'), ['kontak' => 'lestari@contoh.com'] + $this->isianAkun())
            ->assertSessionHasErrors('kontak');

        $this->assertGuest();
        $this->assertSame(0, AkunPengguna::query()->count());
    }

    public function test_a_tutor_can_only_delete_their_own_document(): void
    {
        $pemilik = $this->pendaftarLengkap();
        $lain = $this->pendaftarLengkap();
        $dokumen = $pemilik->dokumen()->first();

        $this->actingAs($lain->akun)->delete(route('tutor.pendaftaran.dokumen.destroy', $dokumen))->assertForbidden();
        $this->assertModelExists($dokumen);

        $this->actingAs($pemilik->akun)->delete(route('tutor.pendaftaran.dokumen.destroy', $dokumen))->assertRedirect();
        $this->assertModelMissing($dokumen);
        Storage::disk('local')->assertMissing($dokumen->berkas);
    }

    public function test_sign_up_is_rate_limited(): void
    {
        foreach (range(1, 10) as $percobaan) {
            $this->post(route('tutor.register.store'), ['kontak' => "x{$percobaan}"] + $this->isianAkun());
        }

        $this->post(route('tutor.register.store'), $this->isianAkun())->assertTooManyRequests();
    }

    public function test_every_step_page_renders(): void
    {
        $tutor = $this->pendaftarLengkap();

        foreach (LangkahPendaftaran::cases() as $langkah) {
            $this->actingAs($tutor->akun)->get(route('tutor.pendaftaran.langkah', $langkah))
                ->assertOk()
                ->assertSeeText('Lembar lamaran')
                ->assertSee('aria-label="Langkah pendaftaran"', false);
        }

        // The step's own loop variables must not rename the save button.
        $this->actingAs($tutor->akun)->get(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Profil))
            ->assertSeeInOrder(['Datang ke rumah siswa', 'Simpan dan lanjut'])
            ->assertDontSee('>Datang ke rumah siswa</button>', false);

        $this->actingAs($tutor->akun)->get(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Dokumen))
            ->assertSeeText('Ijazah terunggah')
            ->assertSee(route('dokumen.berkas', $tutor->dokumen()->first()), false);
    }
}
