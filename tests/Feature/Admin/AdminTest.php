<?php

namespace Tests\Feature\Admin;

use App\Enums\StatusKeluhan;
use App\Enums\StatusPencairan;
use App\Enums\StatusPesanan;
use App\Enums\StatusVerifikasi;
use App\Models\AkunPengguna;
use App\Models\Keluhan;
use App\Models\PencairanDanaTutor;
use App\Models\Pengaturan;
use App\Models\Pesanan;
use App\Models\Siswa;
use App\Models\Tutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    private AkunPengguna $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = AkunPengguna::factory()->admin()->create();
    }

    #[TestWith(['admin.beranda'])]
    #[TestWith(['admin.verifikasi.index'])]
    #[TestWith(['admin.pesanan.index'])]
    #[TestWith(['admin.keluhan.index'])]
    #[TestWith(['admin.ulasan.index'])]
    #[TestWith(['admin.komisi.edit'])]
    #[TestWith(['admin.pencairan.index'])]
    #[TestWith(['admin.pengguna.index'])]
    public function test_every_admin_page_opens_for_admins_and_refuses_parents(string $route): void
    {
        $this->actingAs($this->admin)->get(route($route))->assertOk();
        $this->actingAs(AkunPengguna::factory()->ortu()->create())->get(route($route))->assertForbidden();
    }

    public function test_parent_cannot_post_admin_actions(): void
    {
        $pesanan = $this->pesananBaru();

        $this->actingAs(AkunPengguna::factory()->ortu()->create())
            ->post(route('admin.pesanan.batal', $pesanan), ['alasan' => 'Mencoba membatalkan.'])
            ->assertForbidden();

        $this->assertSame(StatusPesanan::MenungguKonfirmasiTutor, $pesanan->refresh()->status);
    }

    public function test_approving_documents_puts_the_tutor_in_search(): void
    {
        $tutor = Tutor::factory()->eksternal(StatusVerifikasi::Menunggu)->siapDipesan()->create(['nama' => 'Nurul Aisyah']);

        $this->actingAs($this->admin)->get(route('admin.verifikasi.index'))->assertSeeText('Nurul Aisyah');
        $this->get(route('tutor.index'))->assertDontSeeText('Nurul Aisyah');

        $this->actingAs($this->admin)->post(route('admin.verifikasi.setujui', $tutor))->assertRedirect(route('admin.verifikasi.index'));

        $this->assertSame(StatusVerifikasi::Valid, $tutor->dokumen()->first()->status_verifikasi);
        $this->get(route('tutor.index'))->assertSeeText('Nurul Aisyah');
        $this->actingAs($this->admin)->get(route('admin.verifikasi.index'))->assertSeeText('Antrean kosong.');
    }

    public function test_rejecting_documents_records_the_reason_and_keeps_the_tutor_hidden(): void
    {
        $tutor = Tutor::factory()->eksternal(StatusVerifikasi::Menunggu)->siapDipesan()->create(['nama' => 'Bayu Firmansyah']);

        $this->actingAs($this->admin)->post(route('admin.verifikasi.tolak', $tutor), ['alasan' => ''])
            ->assertSessionHasErrors(['alasan' => 'Tulis alasannya. Alasan ini dicatat.']);

        $this->actingAs($this->admin)->post(route('admin.verifikasi.tolak', $tutor), ['alasan' => 'Foto KTP buram.']);

        $dokumen = $tutor->dokumen()->first();
        $this->assertSame(StatusVerifikasi::Ditolak, $dokumen->status_verifikasi);
        $this->assertSame('Foto KTP buram.', $dokumen->alasan_penolakan);
        $this->assertFalse($tutor->isListed());
        $this->get(route('tutor.show', $tutor))->assertNotFound();
    }

    public function test_order_monitoring_filters_by_status_and_tutor(): void
    {
        $dibayar = $this->pesananDibayar();
        $menunggu = $this->pesananBaru();

        $this->actingAs($this->admin)->get(route('admin.pesanan.index', ['status' => 'dibayar']))
            ->assertSeeText($dibayar->id_pesanan)
            ->assertDontSeeText($menunggu->id_pesanan);

        $this->actingAs($this->admin)->get(route('admin.pesanan.index', ['tutor' => $menunggu->id_tutor]))
            ->assertSeeText($menunggu->id_pesanan)
            ->assertDontSeeText($dibayar->id_pesanan);

        $this->actingAs($this->admin)->get(route('admin.pesanan.show', $dibayar))->assertOk()->assertSeeText('Riwayat status');
    }

    public function test_admin_cancels_a_problem_order_with_a_recorded_reason(): void
    {
        $pesanan = $this->pesananDibayar();

        $this->actingAs($this->admin)->post(route('admin.pesanan.batal', $pesanan), ['alasan' => ''])->assertSessionHasErrors('alasan');
        $this->assertSame(StatusPesanan::Dibayar, $pesanan->refresh()->status);

        $this->actingAs($this->admin)->post(route('admin.pesanan.batal', $pesanan), ['alasan' => 'Tutor pindah kota.'])
            ->assertRedirect(route('admin.pesanan.show', $pesanan));

        $this->assertSame(StatusPesanan::Batal, $pesanan->refresh()->status);
        $this->assertSame('Dibatalkan admin: Tutor pindah kota.', $pesanan->riwayat()->latest('id')->value('catatan'));
        $this->assertSame($this->admin->id_akun, $pesanan->riwayat()->latest('id')->value('id_akun'));
    }

    public function test_finished_order_cannot_be_cancelled(): void
    {
        $pesanan = $this->pesananSelesai();

        $this->actingAs($this->admin)->from(route('admin.pesanan.show', $pesanan))
            ->post(route('admin.pesanan.batal', $pesanan), ['alasan' => 'Coba batalkan.'])
            ->assertSessionHas('galat');

        $this->assertSame(StatusPesanan::Selesai, $pesanan->refresh()->status);
    }

    private function keluhan(): Keluhan
    {
        $pesanan = $this->pesananDibayar();
        $keluhan = new Keluhan(['id_pesanan' => $pesanan->id_pesanan, 'jenis' => 'tutor_tidak_hadir', 'deskripsi' => 'Tutor tidak datang.']);
        $keluhan->id_akun = Siswa::query()->whereKey($pesanan->id_siswa)->value('id_akun');
        $keluhan->save();

        return $keluhan;
    }

    public function test_complaint_moves_forward_with_a_reply_the_parent_can_read(): void
    {
        $keluhan = $this->keluhan();

        $this->actingAs($this->admin)->put(route('admin.keluhan.update', $keluhan), ['status' => 'diproses', 'tanggapan_admin' => 'Kami menghubungi tutor hari ini.'])
            ->assertRedirect(route('admin.keluhan.show', $keluhan));
        $this->actingAs($this->admin)->put(route('admin.keluhan.update', $keluhan), ['status' => 'selesai', 'tanggapan_admin' => 'Sesi pengganti dijadwalkan Sabtu.']);

        $this->assertSame(StatusKeluhan::Selesai, $keluhan->refresh()->status);

        $this->actingAs(AkunPengguna::query()->findOrFail($keluhan->id_akun))->get(route('keluhan.index'))
            ->assertSeeText('Sesi pengganti dijadwalkan Sabtu.');
    }

    public function test_complaint_cannot_move_back_and_needs_a_reply(): void
    {
        $keluhan = $this->keluhan();
        $keluhan->forceFill(['status' => 'diproses'])->save();

        $this->actingAs($this->admin)->put(route('admin.keluhan.update', $keluhan), ['status' => 'baru', 'tanggapan_admin' => ''])
            ->assertSessionHasErrors([
                'status' => 'Keluhan berstatus "Diproses" tidak bisa kembali ke "Baru".',
                'tanggapan_admin' => 'Tulis tanggapan untuk orang tua.',
            ]);
    }

    public function test_hiding_a_review_changes_the_tutors_average_rating(): void
    {
        $tutor = $this->tutorSiapDipesan();
        $tutor->jadwal()->create(['hari' => 'sabtu', 'jam' => '08:00:00', 'status' => 'tersedia']);
        $jadwal = $tutor->jadwal()->orderBy('id_jadwal')->pluck('id_jadwal');
        $this->pesananSelesai(tutor: $tutor, ubah: ['id_jadwal' => $jadwal[0]])->ulasan()->create(['nilai' => 5]);
        $buruk = $this->pesananSelesai(tutor: $tutor, ubah: ['id_jadwal' => $jadwal[1]])->ulasan()->create(['nilai' => 1, 'komentar' => 'Hubungi saya di 0812.']);

        $rata = fn (): float => (float) Tutor::query()->denganRating()->findOrFail($tutor->id_tutor)->rata_rating;
        $this->assertSame(3.0, $rata());

        $this->actingAs($this->admin)->put(route('admin.ulasan.update', $buruk), ['ditampilkan' => 0, 'alasan' => ''])->assertSessionHasErrors('alasan');

        $this->actingAs($this->admin)->put(route('admin.ulasan.update', $buruk), ['ditampilkan' => 0, 'alasan' => 'Memuat nomor telepon.']);

        $this->assertFalse($buruk->refresh()->ditampilkan);
        $this->assertSame('Memuat nomor telepon.', $buruk->alasan_moderasi);
        $this->assertSame(5.0, $rata());

        $this->actingAs($this->admin)->put(route('admin.ulasan.update', $buruk), ['ditampilkan' => 1, 'alasan' => 'Nomor sudah dihapus pengulas.']);
        $this->assertSame(3.0, $rata());
    }

    public function test_commission_change_applies_only_to_orders_paid_afterwards(): void
    {
        $lama = $this->pesananDibayar();

        $this->actingAs($this->admin)->put(route('admin.komisi.update'), ['persentase' => '12.5'])->assertRedirect(route('admin.komisi.edit'));
        $baru = $this->pesananDibayar();

        $this->assertSame('12.5', Pengaturan::komisiPersen());
        $this->assertSame(['15.00', '45000.00'], [$lama->komisi()->value('persentase'), $lama->komisi()->value('nominal')]);
        $this->assertSame(['12.50', '37500.00'], [$baru->komisi()->value('persentase'), $baru->komisi()->value('nominal')]);
    }

    public function test_commission_percentage_is_validated(): void
    {
        $this->actingAs($this->admin)->put(route('admin.komisi.update'), ['persentase' => '75'])
            ->assertSessionHasErrors(['persentase' => 'Persentase paling tinggi 50.']);

        $this->assertSame('15', Pengaturan::komisiPersen());
    }

    private function pengajuan(Tutor $tutor, int $nominal): PencairanDanaTutor
    {
        return $tutor->pencairan()->create(['periode' => '2026-11', 'nominal' => $nominal]);
    }

    public function test_payout_is_approved_within_the_balance_then_marked_paid(): void
    {
        // Rp300.000 paid, 15% commission: balance Rp255.000.
        $pesanan = $this->pesananDibayar();
        $tutor = $pesanan->tutor()->firstOrFail();
        $pencairan = $this->pengajuan($tutor, 200000);

        $this->actingAs($this->admin)->get(route('admin.pencairan.index'))->assertSeeText('Rp255.000');

        $this->actingAs($this->admin)->post(route('admin.pencairan.bayar', $pencairan))->assertSessionHas('galat');
        $this->assertSame(StatusPencairan::Menunggu, $pencairan->refresh()->status);

        $this->actingAs($this->admin)->post(route('admin.pencairan.setujui', $pencairan));
        $this->assertSame(StatusPencairan::Disetujui, $pencairan->refresh()->status);

        $this->actingAs($this->admin)->post(route('admin.pencairan.bayar', $pencairan));
        $this->assertSame(StatusPencairan::Dibayar, $pencairan->refresh()->status);

        $this->actingAs($this->admin)->get(route('admin.pencairan.index'))->assertSeeText('Rp55.000');
    }

    public function test_payout_larger_than_the_balance_is_refused(): void
    {
        $tutor = $this->pesananDibayar()->tutor()->firstOrFail();
        $pencairan = $this->pengajuan($tutor, 300000);

        $this->actingAs($this->admin)->post(route('admin.pencairan.setujui', $pencairan))
            ->assertSessionHas('galat', 'Saldo tutor tidak cukup untuk pencairan ini.');

        $this->assertSame(StatusPencairan::Menunggu, $pencairan->refresh()->status);
    }

    public function test_user_list_shows_parents_with_child_and_order_counts(): void
    {
        $siswa = Siswa::factory()->create();
        $this->pesananBaru($siswa);
        $this->pesananDibayar($siswa);
        $ortu = AkunPengguna::query()->findOrFail($siswa->id_akun);

        $response = $this->actingAs($this->admin)->get(route('admin.pengguna.index'));

        $response->assertSeeText($ortu->kontak)->assertDontSeeText($this->admin->kontak);
        $this->assertSame(2, AkunPengguna::query()->withCount('pesanan')->findOrFail($ortu->id_akun)->pesanan_count);
        $this->assertSame(1, Pesanan::query()->milikAkun($ortu->id_akun)->where('status', StatusPesanan::Dibayar)->count());
    }
}
