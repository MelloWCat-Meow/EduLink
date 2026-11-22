<?php

namespace Tests\Feature\Tutor;

use App\Enums\LangkahPendaftaran;
use App\Enums\Peran;
use App\Enums\StatusVerifikasiTutor;
use App\Http\Controllers\Tutor\AktivasiController;
use App\Mail\TautanAktivasiTutor;
use App\Models\AkunPengguna;
use App\Models\MataPelajaran;
use App\Models\ProfilTutor;
use App\Models\Tutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AktivasiTutorTest extends TestCase
{
    use RefreshDatabase;

    private Tutor $tutor;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Storage::fake('local');

        // An internal tutor from the company's data: no account, no rate or district yet.
        $this->tutor = Tutor::factory()->internal()->draf()
            ->has(ProfilTutor::factory()->state(['tarif' => null, 'lokasi' => null]), 'profil')
            ->create(['nama' => 'Lestari Putri', 'kontak' => 'lestari.putri@contoh.com']);
    }

    private function mintaTautan(string $email = 'Lestari.Putri@contoh.com'): string
    {
        $this->post(route('tutor.aktivasi.store'), ['kontak' => $email]);

        $url = null;
        Mail::assertSent(TautanAktivasiTutor::class, function (TautanAktivasiTutor $mail) use (&$url): bool {
            $url = $mail->url;

            return $mail->hasTo('lestari.putri@contoh.com');
        });

        return $url;
    }

    public function test_matching_and_unknown_emails_get_exactly_the_same_answer(): void
    {
        // Outside mode demo; mode demo deliberately shows the link on screen.
        config(['edulink.mode_demo' => false]);

        Tutor::factory()->internal()->denganAkun()->create(['kontak' => 'sudah.aktif@contoh.com']);
        Tutor::factory()->eksternal()->draf()->create(['kontak' => 'eksternal@contoh.com']);

        $jawaban = [];
        foreach (['lestari.putri@contoh.com', 'tidak.ada@contoh.com', 'sudah.aktif@contoh.com', 'eksternal@contoh.com'] as $email) {
            $response = $this->post(route('tutor.aktivasi.store'), ['kontak' => $email]);
            $response->assertRedirect(route('tutor.aktivasi'));
            $jawaban[] = [$response->getStatusCode(), session('status'), session()->has('tautan_demo')];
        }

        $this->assertSame([[302, AktivasiController::PESAN_TERKIRIM, false]], array_values(array_unique($jawaban, SORT_REGULAR)));
        Mail::assertSentCount(1);
    }

    public function test_valid_link_creates_the_account_and_links_the_existing_tutor_without_duplicating_it(): void
    {
        $url = $this->mintaTautan();

        $this->get($url)->assertOk()->assertSeeText('Halo, Lestari Putri');

        $this->post($url, ['password' => 'rahasia123', 'password_confirmation' => 'rahasia123', 'setuju' => '1'])
            ->assertRedirect(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Profil));

        $this->assertSame(1, Tutor::query()->count());
        $akun = AkunPengguna::query()->sole();
        $this->assertSame(Peran::Tutor, $akun->peran);
        $this->assertSame('lestari.putri@contoh.com', $akun->kontak);
        $this->assertSame($akun->id_akun, $this->tutor->refresh()->id_akun);
        $this->assertSame(StatusVerifikasiTutor::Draf, $this->tutor->status_verifikasi);
        $this->assertAuthenticatedAs($akun);

        // The form opens with the company's data already filled in.
        $this->get(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Profil))
            ->assertOk()
            ->assertSee('value="Universitas Jambi"', false)
            ->assertSeeText('lengkapi tarif per jam');
    }

    public function test_link_expires_after_sixty_minutes(): void
    {
        $url = $this->mintaTautan();

        $this->travel(61)->minutes();

        $this->get($url)->assertForbidden()->assertSeeText('Tautan tidak berlaku');
        $this->post($url, ['password' => 'rahasia123', 'password_confirmation' => 'rahasia123', 'setuju' => '1'])->assertForbidden();
        $this->assertNull($this->tutor->refresh()->id_akun);
    }

    public function test_link_works_only_once(): void
    {
        $url = $this->mintaTautan();
        $this->post($url, ['password' => 'rahasia123', 'password_confirmation' => 'rahasia123', 'setuju' => '1']);
        $this->post(route('logout'));

        $this->get($url)->assertStatus(410)->assertSeeText('Tautan sudah dipakai');
        $this->post($url, ['password' => 'lainlagi123', 'password_confirmation' => 'lainlagi123', 'setuju' => '1'])->assertStatus(410);

        $this->assertSame(1, AkunPengguna::query()->count());
    }

    public function test_tampered_link_is_refused(): void
    {
        $url = $this->mintaTautan();
        $lain = Tutor::factory()->internal()->draf()->create();

        $this->get(str_replace($this->tutor->id_tutor, $lain->id_tutor, $url))->assertForbidden();
    }

    public function test_internal_tutor_may_send_without_documents_but_not_without_a_schedule(): void
    {
        $url = $this->mintaTautan();
        $this->post($url, ['password' => 'rahasia123', 'password_confirmation' => 'rahasia123', 'setuju' => '1']);

        $this->put(route('tutor.pendaftaran.profil'), [
            'pendidikan' => 'S1', 'institusi' => 'Universitas Jambi', 'jurusan' => 'Pendidikan Matematika',
            'kompetensi' => 'Pengajar kelas reguler SMP di bimbel EduLink.', 'pengalaman' => 'Empat tahun mengajar kelas reguler bimbel.',
            'jenjang' => ['SMP'], 'mapel' => [MataPelajaran::factory()->create(['jenjang' => 'SMP'])->kode_mapel],
            'tarif' => '80000', 'lokasi' => 'Kota Baru', 'layanan' => ['online'],
        ])->assertSessionHasNoErrors();

        $this->post(route('tutor.pendaftaran.kirim'))->assertRedirect(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Kirim));
        $this->get(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Kirim))
            ->assertSeeText('Tambahkan minimal satu jadwal mingguan.')
            ->assertDontSeeText('Ijazah wajib diunggah.');

        $this->put(route('tutor.pendaftaran.jadwal'), ['slot' => [['hari' => 'rabu', 'jam' => '19:00']]]);
        $this->post(route('tutor.pendaftaran.kirim'))->assertRedirect(route('tutor.status'));

        $this->assertSame(StatusVerifikasiTutor::Menunggu, $this->tutor->refresh()->status_verifikasi);
        $this->assertSame(1, Tutor::query()->count());
    }

    public function test_activation_requests_are_rate_limited_per_email(): void
    {
        foreach (range(1, 3) as $percobaan) {
            $this->post(route('tutor.aktivasi.store'), ['kontak' => 'lestari.putri@contoh.com'])->assertRedirect();
        }

        $this->post(route('tutor.aktivasi.store'), ['kontak' => 'lestari.putri@contoh.com'])->assertTooManyRequests();
    }

    public function test_sign_in_and_sign_up_pages_link_to_activation(): void
    {
        $this->get(route('login'))->assertSee(route('tutor.aktivasi'), false)->assertSeeText('Sudah mengajar di bimbel EduLink?');
        $this->get(route('tutor.register'))->assertSee(route('tutor.aktivasi'), false)->assertSeeText('Sudah mengajar di bimbel EduLink?');
    }
}
