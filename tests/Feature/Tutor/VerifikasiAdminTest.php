<?php

namespace Tests\Feature\Tutor;

use App\Enums\JenisDokumen;
use App\Enums\LangkahPendaftaran;
use App\Enums\StatusTutor;
use App\Enums\StatusVerifikasi;
use App\Enums\StatusVerifikasiTutor;
use App\Jobs\SinkronTutorKeEduManage;
use App\Mail\KeputusanVerifikasiTutor;
use App\Models\AkunPengguna;
use App\Models\Tutor;
use App\Services\PendaftaranTutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\MembuatPendaftar;
use Tests\TestCase;

class VerifikasiAdminTest extends TestCase
{
    use MembuatPendaftar, RefreshDatabase;

    private AkunPengguna $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Mail::fake();
        Queue::fake();

        $this->admin = AkunPengguna::factory()->admin()->create(['nama' => 'Dewi Lestari']);
    }

    /**
     * @param  array<string, mixed>  $profil
     */
    private function pendaftarMenunggu(array $profil = [], StatusTutor $status = StatusTutor::Eksternal): Tutor
    {
        $tutor = $this->pendaftarLengkap($profil, $status);
        app(PendaftaranTutor::class)->kirim($tutor);

        return $tutor->refresh();
    }

    public function test_queue_lists_only_waiting_applications_oldest_first_with_their_kind(): void
    {
        $baru = $this->pendaftarMenunggu();
        $baru->forceFill(['nama' => 'Dikirim Kemarin', 'diajukan_pada' => now()->subDay()])->save();
        $lama = $this->pendaftarMenunggu(status: StatusTutor::Internal);
        $lama->forceFill(['nama' => 'Dikirim Seminggu Lalu', 'diajukan_pada' => now()->subWeek()])->save();
        Tutor::factory()->draf()->create(['nama' => 'Masih Draf']);
        Tutor::factory()->perluPerbaikan()->create(['nama' => 'Sedang Diperbaiki']);

        $this->actingAs($this->admin)->get(route('admin.verifikasi.index'))
            ->assertOk()
            ->assertSeeTextInOrder(['Dikirim Seminggu Lalu', 'Internal', 'Dikirim Kemarin', 'Eksternal'])
            ->assertDontSeeText('Masih Draf')
            ->assertDontSeeText('Sedang Diperbaiki');
    }

    public function test_detail_shows_the_application_and_openable_documents(): void
    {
        $tutor = $this->pendaftarMenunggu();

        $this->actingAs($this->admin)->get(route('admin.verifikasi.show', $tutor))
            ->assertOk()
            ->assertSeeText($tutor->kontak)
            ->assertSeeText('S1 Pendidikan Matematika, Universitas Jambi')
            ->assertSeeText('Rp75.000 / jam')
            ->assertSeeText('Online atau datang ke rumah')
            ->assertSeeText($tutor->jadwal()->first()->label())
            ->assertSee(route('dokumen.berkas', $tutor->dokumen()->first()), false)
            ->assertSeeText('Dikirim tutor');
    }

    public function test_approving_validates_documents_lists_the_tutor_records_the_decision_emails_and_syncs(): void
    {
        $tutor = $this->pendaftarMenunggu();
        $this->assertFalse($tutor->isListed());

        $this->actingAs($this->admin)->post(route('admin.verifikasi.setujui', $tutor))->assertRedirect(route('admin.verifikasi.index'));

        $tutor->refresh();
        $this->assertSame(StatusVerifikasiTutor::Terverifikasi, $tutor->status_verifikasi);
        $this->assertTrue($tutor->dokumen()->get()->every(fn ($dokumen) => $dokumen->status_verifikasi === StatusVerifikasi::Valid));
        $this->assertTrue($tutor->isListed());
        $this->get(route('tutor.index'))->assertSeeText($tutor->nama);
        $this->assertDatabaseHas('riwayat_verifikasi_tutor', ['id_tutor' => $tutor->id_tutor, 'status' => 'terverifikasi', 'id_akun' => $this->admin->id_akun]);
        Mail::assertSent(KeputusanVerifikasiTutor::class, fn ($mail) => $mail->hasTo($tutor->kontak) && $mail->keputusan === StatusVerifikasiTutor::Terverifikasi);
        Queue::assertPushed(SinkronTutorKeEduManage::class, fn ($job) => $job->idTutor === $tutor->id_tutor);
    }

    public function test_sync_job_writes_the_tutor_payload_to_the_edumanage_log(): void
    {
        $tutor = $this->pendaftarLengkap();
        $kodeMapel = $tutor->mataPelajaran()->pluck('mata_pelajaran.kode_mapel')->all();

        Log::shouldReceive('channel')->once()->with('edumanage')->andReturnSelf();
        Log::shouldReceive('info')->once()->with('Sinkron tutor ke EduManage', Mockery::on(fn (array $payload): bool => $payload['id_tutor'] === $tutor->id_tutor
            && $payload['nama'] === $tutor->nama
            && $payload['kontak'] === $tutor->kontak
            && $payload['status'] === 'eksternal'
            && $payload['mapel'] === $kodeMapel));

        (new SinkronTutorKeEduManage($tutor->id_tutor))->handle();
    }

    public function test_requesting_corrections_flags_sections_and_documents_then_the_tutor_fixes_and_resends(): void
    {
        $tutor = $this->pendaftarMenunggu();
        $ktp = $tutor->dokumen()->where('jenis', JenisDokumen::Ktp)->sole();
        $ijazah = $tutor->dokumen()->where('jenis', JenisDokumen::Ijazah)->sole();

        $this->actingAs($this->admin)->post(route('admin.verifikasi.perbaikan', $tutor), [
            'catatan' => 'Dua hal perlu diperbaiki.',
            'bagian' => ['profil'],
            'catatan_bagian' => ['profil' => 'Pengalaman terlalu singkat.', 'jadwal' => 'tidak dicentang, jadi diabaikan'],
            'dokumen' => [$ktp->id_dokumen],
            'alasan_dokumen' => [$ktp->id_dokumen => 'Foto KTP buram.'],
        ])->assertRedirect(route('admin.verifikasi.index'));

        $tutor->refresh();
        $this->assertSame(StatusVerifikasiTutor::PerluPerbaikan, $tutor->status_verifikasi);
        $this->assertSame(['profil' => 'Pengalaman terlalu singkat.', 'dokumen' => null], $tutor->bagian_perbaikan);
        $this->assertSame('Dua hal perlu diperbaiki.', $tutor->catatan_verifikasi);
        $this->assertSame(StatusVerifikasi::Ditolak, $ktp->refresh()->status_verifikasi);
        $this->assertSame('Foto KTP buram.', $ktp->alasan_penolakan);
        $this->assertSame(StatusVerifikasi::Valid, $ijazah->refresh()->status_verifikasi);
        $this->assertDatabaseHas('riwayat_verifikasi_tutor', ['id_tutor' => $tutor->id_tutor, 'status' => 'perlu_perbaikan', 'id_akun' => $this->admin->id_akun]);
        Mail::assertSent(KeputusanVerifikasiTutor::class, fn ($mail) => $mail->keputusan === StatusVerifikasiTutor::PerluPerbaikan);

        // The tutor sees the red correction and cannot resend before fixing.
        $this->post(route('logout'));
        $this->actingAs($tutor->akun)->get(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Profil))
            ->assertSeeText('Pengalaman terlalu singkat.');
        $this->actingAs($tutor->akun)->post(route('tutor.pendaftaran.kirim'))->assertRedirect(route('tutor.pendaftaran.langkah', LangkahPendaftaran::Kirim));
        $this->assertSame(StatusVerifikasiTutor::PerluPerbaikan, $tutor->refresh()->status_verifikasi);

        $this->actingAs($tutor->akun)->put(route('tutor.pendaftaran.profil'), $this->isianProfil(['pengalaman' => 'Tiga tahun les privat SMP dan dua tahun asisten guru di SMPN 7 Jambi.']));
        $this->actingAs($tutor->akun)->post(route('tutor.pendaftaran.dokumen.store'), ['jenis' => 'ktp', 'berkas' => $this->pdf('ktp.pdf')]);

        $this->assertNull($tutor->refresh()->bagian_perbaikan);
        $this->actingAs($tutor->akun)->post(route('tutor.pendaftaran.kirim'))->assertRedirect(route('tutor.status'));

        $tutor->refresh();
        $this->assertSame(StatusVerifikasiTutor::Menunggu, $tutor->status_verifikasi);
        $this->actingAs($this->admin)->get(route('admin.verifikasi.index'))->assertSeeText($tutor->nama);
    }

    public function test_requesting_corrections_needs_a_flag_a_note_and_a_reason_per_flagged_document(): void
    {
        $tutor = $this->pendaftarMenunggu();
        $ktp = $tutor->dokumen()->where('jenis', JenisDokumen::Ktp)->sole();

        $this->actingAs($this->admin)->post(route('admin.verifikasi.perbaikan', $tutor), ['catatan' => 'Ada yang salah.'])
            ->assertSessionHasErrors(['bagian' => 'Tandai minimal satu bagian atau dokumen yang perlu diperbaiki.']);

        $this->actingAs($this->admin)->post(route('admin.verifikasi.perbaikan', $tutor), ['bagian' => ['profil']])
            ->assertSessionHasErrors('catatan');

        $this->actingAs($this->admin)->post(route('admin.verifikasi.perbaikan', $tutor), ['catatan' => 'Ada yang salah.', 'dokumen' => [$ktp->id_dokumen]])
            ->assertSessionHasErrors("alasan_dokumen.{$ktp->id_dokumen}");

        $this->assertSame(StatusVerifikasiTutor::Menunggu, $tutor->refresh()->status_verifikasi);
        Mail::assertNothingSent();
    }

    public function test_rejecting_needs_a_reason_is_final_and_is_emailed(): void
    {
        $tutor = $this->pendaftarMenunggu();

        $this->actingAs($this->admin)->post(route('admin.verifikasi.tolak', $tutor), ['alasan' => ''])
            ->assertSessionHasErrors(['alasan' => 'Tulis alasannya. Alasan ini dicatat.']);
        $this->assertSame(StatusVerifikasiTutor::Menunggu, $tutor->refresh()->status_verifikasi);

        $this->actingAs($this->admin)->post(route('admin.verifikasi.tolak', $tutor), ['alasan' => 'Ijazah tidak sesuai mapel yang dipilih.']);

        $tutor->refresh();
        $this->assertSame(StatusVerifikasiTutor::Ditolak, $tutor->status_verifikasi);
        $this->assertSame('Ijazah tidak sesuai mapel yang dipilih.', $tutor->catatan_verifikasi);
        $this->assertFalse($tutor->isListed());
        Mail::assertSent(KeputusanVerifikasiTutor::class, fn ($mail) => $mail->keputusan === StatusVerifikasiTutor::Ditolak);
        Queue::assertNothingPushed();

        $this->post(route('logout'));
        $this->actingAs($tutor->akun)->post(route('tutor.pendaftaran.kirim'))->assertRedirect(route('tutor.status'));
        $this->assertSame(StatusVerifikasiTutor::Ditolak, $tutor->refresh()->status_verifikasi);
    }

    public function test_a_decision_on_an_application_that_is_not_waiting_is_refused(): void
    {
        $tutor = $this->pendaftarLengkap();

        $this->actingAs($this->admin)->from(route('admin.verifikasi.show', $tutor))->post(route('admin.verifikasi.setujui', $tutor))
            ->assertRedirect(route('admin.verifikasi.show', $tutor))
            ->assertSessionHas('galat');

        $this->assertSame(StatusVerifikasiTutor::Draf, $tutor->refresh()->status_verifikasi);
        Queue::assertNothingPushed();
    }

    #[TestWith(['tarif'])]
    #[TestWith(['lokasi'])]
    public function test_verified_tutor_with_an_empty_rate_or_district_is_not_listed(string $kolom): void
    {
        $lengkap = Tutor::factory()->siapDipesan()->create(['nama' => 'Profil Lengkap']);
        $kosong = Tutor::factory()->siapDipesan([$kolom => null])->create(['nama' => 'Profil Bolong']);

        $this->assertSame(StatusVerifikasiTutor::Terverifikasi, $kosong->status_verifikasi);
        $this->get(route('tutor.index'))->assertSeeText('Profil Lengkap')->assertDontSeeText('Profil Bolong');
        $this->get(route('tutor.show', $kosong))->assertNotFound();
        $this->assertFalse($kosong->isListed());
        $this->assertTrue($lengkap->isListed());
    }
}
