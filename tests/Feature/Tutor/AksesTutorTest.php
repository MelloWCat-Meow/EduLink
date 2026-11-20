<?php

namespace Tests\Feature\Tutor;

use App\Enums\LangkahPendaftaran;
use App\Models\AkunPengguna;
use App\Models\Pesanan;
use App\Models\Tutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\MembuatPendaftar;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class AksesTutorTest extends TestCase
{
    use MembuatPendaftar, MembuatPesanan, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function masuk(Tutor $tutor): TestResponse
    {
        return $this->post(route('login.store'), ['kontak' => $tutor->kontak, 'password' => 'password']);
    }

    public function test_draft_tutor_signs_in_to_the_first_incomplete_step(): void
    {
        $tutor = $this->pendaftarLengkap(denganDokumen: false);

        $this->masuk($tutor)->assertRedirect(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Dokumen));
    }

    public function test_tutor_asked_to_fix_signs_in_to_the_flagged_step_and_sees_the_correction(): void
    {
        $tutor = $this->pendaftarLengkap();
        $tutor->forceFill(['status_verifikasi' => 'perlu_perbaikan', 'bagian_perbaikan' => ['jadwal' => 'Tambahkan jadwal akhir pekan.'], 'catatan_verifikasi' => 'Hampir lengkap.'])->save();

        $this->masuk($tutor)->assertRedirect(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Jadwal));

        $this->get(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Jadwal))
            ->assertSeeText('Koreksi tim akademik')
            ->assertSeeText('Tambahkan jadwal akhir pekan.')
            ->assertSeeText('Hampir lengkap.')
            ->assertSeeText('perlu diperbaiki');
    }

    #[TestWith(['menunggu', 'Sedang diperiksa'])]
    #[TestWith(['ditolak', 'belum diterima'])]
    public function test_waiting_and_rejected_tutors_sign_in_to_the_status_page(string $status, string $teks): void
    {
        $tutor = $this->pendaftarLengkap();
        $tutor->forceFill(['status_verifikasi' => $status, 'catatan_verifikasi' => 'Kompetensi belum sesuai mapel.', 'diajukan_pada' => now()])->save();

        $this->masuk($tutor)->assertRedirect(route('tutor.status'));

        $halaman = $this->get(route('tutor.status'))->assertOk()->assertSeeText($teks);

        if ($status === 'menunggu') {
            $halaman->assertSeeText('Isian yang dikirim')->assertSeeText($tutor->nama)->assertDontSee(route('tutor.pendaftaran.kirim'), false)->assertDontSeeText('Ubah');
        } else {
            $halaman->assertSeeText('Kompetensi belum sesuai mapel.')->assertSeeText('tidak bisa diajukan ulang')->assertDontSee(route('tutor.pendaftaran.kirim'), false);
            $this->post(route('tutor.pendaftaran.kirim'))->assertRedirect(route('tutor.status'));
        }
    }

    public function test_verified_tutor_signs_in_to_the_tutor_area_with_a_link_to_the_public_profile(): void
    {
        $tutor = Tutor::factory()->eksternal()->siapDipesan()->denganAkun()->create(['nama' => 'Rina Marlina']);

        $this->masuk($tutor)->assertRedirect(route('tutor.area'));

        $this->get(route('tutor.area'))
            ->assertOk()
            ->assertSeeText('Selamat datang, Rina Marlina')
            ->assertSeeText('Yang perlu Anda lakukan')
            ->assertSee(route('tutor.show', $tutor), false)
            ->assertSee(route('tutor.pesanan.index'), false);
    }

    public function test_tutor_is_sent_to_their_own_page_instead_of_a_remembered_url(): void
    {
        $tutor = Tutor::factory()->siapDipesan()->denganAkun()->create();

        $this->get(route('pesanan.index'));
        $this->masuk($tutor)->assertRedirect(route('tutor.area'));
    }

    public function test_unverified_tutor_only_reaches_the_form_and_status_pages(): void
    {
        $draf = $this->pendaftarLengkap();
        $menunggu = $this->pendaftarLengkap();
        $menunggu->forceFill(['status_verifikasi' => 'menunggu'])->save();

        $this->actingAs($draf->akun)->get(route('tutor.area'))->assertRedirect(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Kirim));
        $this->actingAs($draf->akun)->get(route('tutor.status'))->assertRedirect(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Kirim));
        $this->actingAs($menunggu->akun)->get(route('tutor.area'))->assertRedirect(route('tutor.status'));
        $this->actingAs($menunggu->akun)->get(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Profil))->assertRedirect(route('tutor.status'));
    }

    public function test_tutor_is_refused_on_parent_and_admin_pages(): void
    {
        $tutor = Tutor::factory()->siapDipesan()->denganAkun()->create();
        $pesanan = $this->pesananBaru();

        foreach ([route('beranda'), route('anak.index'), route('pesanan.index'), route('pesanan.show', $pesanan), route('admin.beranda'), route('admin.verifikasi.index')] as $url) {
            $this->actingAs($tutor->akun)->get($url)->assertForbidden();
        }

        $this->assertInstanceOf(Pesanan::class, $pesanan);
    }

    #[TestWith(['ortu'])]
    #[TestWith(['admin'])]
    public function test_parents_and_admins_are_refused_on_tutor_pages(string $peran): void
    {
        $akun = AkunPengguna::factory()->{$peran}()->create();

        $this->actingAs($akun)->get(route('tutor.area'))->assertForbidden();
        $this->actingAs($akun)->get(route('tutor.status'))->assertForbidden();
        $this->actingAs($akun)->get(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Profil))->assertForbidden();
        $this->actingAs($akun)->put(route('tutor.pendaftaran.jadwal'), ['slot' => [['hari' => 'senin', 'jam' => '16:00']]])->assertForbidden();
    }

    public function test_documents_open_only_for_their_tutor_and_admins(): void
    {
        $pemilik = $this->pendaftarLengkap();
        $lain = $this->pendaftarLengkap();
        $dokumen = $pemilik->dokumen()->first();

        $this->actingAs($pemilik->akun)->get(route('dokumen.berkas', $dokumen))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->actingAs(AkunPengguna::factory()->admin()->create())->get(route('dokumen.berkas', $dokumen))->assertOk();

        $this->actingAs($lain->akun)->get(route('dokumen.berkas', $dokumen))->assertForbidden();
        $this->actingAs(AkunPengguna::factory()->ortu()->create())->get(route('dokumen.berkas', $dokumen))->assertForbidden();

        $this->post(route('logout'));
        $this->get(route('dokumen.berkas', $dokumen))->assertRedirect(route('login'));
    }
}
