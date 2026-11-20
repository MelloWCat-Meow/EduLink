<?php

namespace Tests\Feature\Tutor;

use App\Enums\Hari;
use App\Enums\StatusJadwal;
use App\Enums\StatusPencairan;
use App\Enums\StatusPesanan;
use App\Models\AkunPengguna;
use App\Models\JadwalKetersediaan;
use App\Models\MataPelajaran;
use App\Models\Pesanan;
use App\Models\Tutor;
use App\Services\SaldoTutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class DasborTutorTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    private Tutor $tutor;

    private AkunPengguna $akun;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tutor = Tutor::factory()->siapDipesan(['tarif' => '80000.00'])->denganAkun()->create(['nama' => 'Rina Marlina']);
        $this->akun = $this->tutor->akun;
        $this->tutor->load('profil', 'mataPelajaran', 'jadwal');
    }

    public function test_every_dashboard_page_opens_for_the_tutor(): void
    {
        $pesanan = $this->pesananBaru(tutor: $this->tutor);

        foreach (['tutor.area', 'tutor.pesanan.index', 'tutor.sesi.index', 'tutor.jadwal.index', 'tutor.profil.edit', 'tutor.ulasan.index', 'tutor.pendapatan.index', 'tutor.keluhan.index'] as $route) {
            $this->actingAs($this->akun)->get(route($route))->assertOk();
        }

        $this->actingAs($this->akun)->get(route('tutor.area'))
            ->assertSeeText('Permintaan pesanan (1)')
            ->assertSee('data-hitung-mundur', false);
        $this->actingAs($this->akun)->get(route('tutor.pesanan.show', $pesanan))
            ->assertOk()
            ->assertSeeText($pesanan->siswa->nama)
            ->assertSeeText('Sisa waktu menjawab')
            ->assertSeeText('Terima pesanan');
    }

    public function test_tutor_accepts_a_request(): void
    {
        $pesanan = $this->pesananBaru(tutor: $this->tutor);

        $this->actingAs($this->akun)->post(route('tutor.pesanan.terima', $pesanan))->assertRedirect(route('tutor.pesanan.show', $pesanan));

        $this->assertSame(StatusPesanan::MenungguPembayaran, $pesanan->refresh()->status);
        $this->assertDatabaseHas('riwayat_status_pesanan', ['id_pesanan' => $pesanan->id_pesanan, 'status' => 'menunggu_pembayaran', 'id_akun' => $this->akun->id_akun]);
    }

    public function test_tutor_rejects_a_request_with_a_reason_and_the_slot_opens_again(): void
    {
        $pesanan = $this->pesananBaru(tutor: $this->tutor);

        $this->actingAs($this->akun)->post(route('tutor.pesanan.tolak', $pesanan), ['alasan' => ''])->assertSessionHasErrors('alasan');
        $this->assertSame(StatusPesanan::MenungguKonfirmasiTutor, $pesanan->refresh()->status);

        $this->actingAs($this->akun)->post(route('tutor.pesanan.tolak', $pesanan), ['alasan' => 'Jadwal bentrok dengan les lain.']);

        $this->assertSame(StatusPesanan::Ditolak, $pesanan->refresh()->status);
        $this->assertSame(StatusJadwal::Tersedia, $pesanan->jadwal()->first()->status);
        $this->assertDatabaseHas('riwayat_status_pesanan', ['id_pesanan' => $pesanan->id_pesanan, 'status' => 'ditolak', 'catatan' => 'Ditolak tutor: Jadwal bentrok dengan les lain.']);
    }

    public function test_answering_after_the_deadline_is_refused_and_the_order_expires(): void
    {
        $pesanan = $this->pesananBaru(tutor: $this->tutor);
        $this->travel(25)->hours();

        $this->actingAs($this->akun)->from(route('tutor.pesanan.show', $pesanan))->post(route('tutor.pesanan.terima', $pesanan))
            ->assertRedirect(route('tutor.pesanan.show', $pesanan))
            ->assertSessionHas('kode_galat', 'TRANSISI_TIDAK_SAH');

        $this->assertSame(StatusPesanan::Batal, $pesanan->refresh()->status);
    }

    public function test_accepting_an_order_in_the_wrong_status_is_refused(): void
    {
        $pesanan = $this->pesananDibayar(tutor: $this->tutor);

        $this->actingAs($this->akun)->post(route('tutor.pesanan.terima', $pesanan))->assertSessionHas('kode_galat', 'TRANSISI_TIDAK_SAH');
        $this->actingAs($this->akun)->post(route('tutor.pesanan.tolak', $pesanan), ['alasan' => 'Tidak jadi mengajar.'])->assertSessionHas('kode_galat');

        $this->assertSame(StatusPesanan::Dibayar, $pesanan->refresh()->status);
    }

    public function test_tutor_cancels_only_while_waiting_for_payment(): void
    {
        $menunggu = $this->pesananMenungguPembayaran(tutor: $this->tutor);

        $this->actingAs($this->akun)->post(route('tutor.pesanan.batal', $menunggu), ['alasan' => 'Sakit dua minggu ke depan.']);
        $this->assertSame(StatusPesanan::Batal, $menunggu->refresh()->status);
        $this->assertDatabaseHas('riwayat_status_pesanan', ['id_pesanan' => $menunggu->id_pesanan, 'catatan' => 'Dibatalkan tutor: Sakit dua minggu ke depan.']);

        $baru = $this->pesananBaru(tutor: $this->tutor);
        $this->actingAs($this->akun)->post(route('tutor.pesanan.batal', $baru), ['alasan' => 'Tidak jadi.'])->assertSessionHas('galat');
        $this->assertSame(StatusPesanan::MenungguKonfirmasiTutor, $baru->refresh()->status);

        $dibayar = $this->pesananDibayar(tutor: Tutor::factory()->siapDipesan()->create()->load('profil', 'mataPelajaran', 'jadwal'));
        $dibayar->forceFill(['id_tutor' => $this->tutor->id_tutor])->save();
        $this->actingAs($this->akun)->post(route('tutor.pesanan.batal', $dibayar), ['alasan' => 'Tidak jadi.'])
            ->assertSessionHas('galat', fn (string $pesan) => str_contains($pesan, 'ajukan keluhan'));
        $this->assertSame(StatusPesanan::Dibayar, $dibayar->refresh()->status);
    }

    public function test_tutor_cannot_see_or_act_on_another_tutors_orders(): void
    {
        $milikLain = $this->pesananBaru();

        $this->actingAs($this->akun)->get(route('tutor.pesanan.show', $milikLain))->assertForbidden();
        $this->actingAs($this->akun)->post(route('tutor.pesanan.terima', $milikLain))->assertForbidden();
        $this->actingAs($this->akun)->post(route('tutor.pesanan.keluhan', $milikLain), ['jenis' => 'lainnya', 'deskripsi' => 'Mencoba pesanan orang lain.'])->assertForbidden();
        $this->actingAs($this->akun)->get(route('tutor.pesanan.index'))->assertDontSeeText($milikLain->id_pesanan);

        $sesiLain = $this->pesananDibayar()->sesi()->first();
        $this->actingAs($this->akun)->post(route('tutor.sesi.catat', $sesiLain), ['kehadiran' => 'hadir'])->assertForbidden();
        $this->assertSame(StatusPesanan::MenungguKonfirmasiTutor, $milikLain->refresh()->status);
    }

    public function test_a_session_can_be_logged_only_once_it_has_started_and_the_order_finishes_after_the_last_one(): void
    {
        $pesanan = $this->pesananDibayar(tutor: $this->tutor, ubah: ['jumlah_pertemuan' => 2]);
        [$pertama, $kedua] = $pesanan->sesi()->orderBy('waktu')->get();

        $this->actingAs($this->akun)->post(route('tutor.sesi.catat', $pertama), ['kehadiran' => 'hadir'])
            ->assertSessionHas('galat', fn (string $pesan) => str_starts_with($pesan, 'Kehadiran baru bisa dicatat setelah sesi dimulai'));
        $this->assertNull($pertama->refresh()->kehadiran);

        $this->travelTo($pertama->waktu->copy()->addMinutes(10));
        $this->actingAs($this->akun)->post(route('tutor.sesi.catat', $pertama), ['kehadiran' => 'hadir', 'catatan' => 'Bab 1 pecahan.']);
        $this->assertSame('Bab 1 pecahan.', $pertama->refresh()->catatan);
        $this->assertSame(StatusPesanan::Dibayar, $pesanan->refresh()->status);

        $this->travelTo($kedua->waktu->copy()->addMinutes(10));
        $this->actingAs($this->akun)->post(route('tutor.sesi.catat', $kedua), ['kehadiran' => 'tidak_hadir'])
            ->assertSessionHas('status', fn (string $pesan) => str_contains($pesan, 'pesanan selesai'));

        $this->assertSame(StatusPesanan::Selesai, $pesanan->refresh()->status);
        $this->actingAs($this->akun)->post(route('tutor.sesi.catat', $kedua), ['kehadiran' => 'hadir'])->assertSessionHas('galat');
    }

    public function test_tutor_adds_changes_and_deletes_free_slots(): void
    {
        $this->actingAs($this->akun)->post(route('tutor.jadwal.store'), ['hari' => 'kamis', 'jam' => '18:30'])->assertSessionHas('status');
        $slot = $this->tutor->jadwal()->where('hari', 'kamis')->where('jam', '18:30:00')->sole();
        $this->assertSame('18:30:00', $slot->jam);

        $this->actingAs($this->akun)->put(route('tutor.jadwal.update', $slot), ['hari' => 'jumat', 'jam' => '20:15']);
        $this->assertSame(Hari::Jumat, $slot->refresh()->hari);

        $this->actingAs($this->akun)->delete(route('tutor.jadwal.destroy', $slot));
        $this->assertModelMissing($slot);
    }

    public function test_a_duplicate_slot_is_refused(): void
    {
        $ada = $this->tutor->jadwal->first();
        $isian = ['hari' => $ada->hari->value, 'jam' => substr($ada->jam, 0, 5)];

        $this->actingAs($this->akun)->post(route('tutor.jadwal.store'), $isian)
            ->assertSessionHas('galat', fn (string $pesan) => str_contains($pesan, 'sudah ada'));

        $lain = $this->tutor->jadwal()->create(['hari' => 'minggu', 'jam' => '08:00:00', 'status' => 'tersedia']);
        $this->actingAs($this->akun)->put(route('tutor.jadwal.update', $lain), $isian)->assertSessionHas('galat');

        $this->assertSame(2, $this->tutor->jadwal()->count());
        $this->assertSame(Hari::Minggu, $lain->refresh()->hari);
    }

    #[TestWith(['pesananBaru', 'ditahan'])]
    #[TestWith(['pesananDibayar', 'terisi'])]
    public function test_held_or_filled_slots_cannot_be_changed_or_deleted(string $buat, string $kata): void
    {
        $pesanan = $this->{$buat}(tutor: $this->tutor);
        $slot = $pesanan->jadwal()->first();
        $sebelum = [$slot->hari, $slot->jam];

        $this->actingAs($this->akun)->put(route('tutor.jadwal.update', $slot), ['hari' => 'minggu', 'jam' => '07:00'])
            ->assertSessionHas('galat', fn (string $pesan) => str_contains($pesan, $kata));
        $this->actingAs($this->akun)->delete(route('tutor.jadwal.destroy', $slot))->assertSessionHas('galat');

        $this->assertModelExists($slot);
        $this->assertSame($sebelum, [$slot->refresh()->hari, $slot->jam]);
        $this->actingAs($this->akun)->get(route('tutor.jadwal.index'))->assertSeeText('belum bisa diubah atau dihapus');
    }

    public function test_a_freed_slot_with_order_history_is_switched_off_instead_of_rewritten(): void
    {
        $pesanan = $this->pesananBaru(tutor: $this->tutor);
        $this->actingAs($this->akun)->post(route('tutor.pesanan.tolak', $pesanan), ['alasan' => 'Tidak bisa hari itu.']);
        $slot = $pesanan->jadwal()->first();
        $labelLama = $slot->label();

        $this->actingAs($this->akun)->put(route('tutor.jadwal.update', $slot), ['hari' => 'minggu', 'jam' => '07:00'])->assertSessionHas('status');

        $this->assertSame(StatusJadwal::Nonaktif, $slot->refresh()->status);
        $this->assertSame($labelLama, $pesanan->refresh()->jadwal->label());
        $this->assertTrue($this->tutor->jadwal()->where('hari', 'minggu')->where('status', 'tersedia')->exists());
        $this->actingAs($this->akun)->get(route('tutor.jadwal.index'))->assertDontSeeText($labelLama);
    }

    public function test_slots_of_another_tutor_cannot_be_touched(): void
    {
        $slotLain = JadwalKetersediaan::factory()->create();

        $this->actingAs($this->akun)->delete(route('tutor.jadwal.destroy', $slotLain))->assertForbidden();
        $this->assertModelExists($slotLain);
    }

    /**
     * @return array<string, mixed>
     */
    private function isianProfil(array $ubah = []): array
    {
        return array_merge([
            'tarif' => '95.000', 'lokasi' => 'Kota Baru', 'layanan' => ['online'],
            'kompetensi' => 'Matematika SMP dengan contoh sehari-hari.', 'pengalaman' => 'Enam tahun les privat SD dan SMP.',
            'no_hp' => '0812 6610 0201',
        ], $ubah);
    }

    public function test_profile_changes_apply_without_reverification_and_the_rate_only_to_new_orders(): void
    {
        $lama = $this->pesananBaru(tutor: $this->tutor);
        $totalLama = $lama->total_biaya;

        $this->actingAs($this->akun)->put(route('tutor.profil.update'), $this->isianProfil())->assertRedirect(route('tutor.profil.edit'));

        $this->tutor->refresh()->load('profil');
        $this->assertSame('95000.00', $this->tutor->profil->tarif);
        $this->assertSame('Kota Baru', $this->tutor->profil->lokasi);
        $this->assertFalse($this->tutor->profil->melayani_kunjungan);
        $this->assertSame('081266100201', $this->tutor->no_hp);
        $this->assertTrue($this->tutor->isListed());

        $this->assertSame('80000.00', $lama->refresh()->tarif_per_jam);
        $this->assertSame($totalLama, $lama->total_biaya);

        $this->tutor->jadwal()->create(['hari' => 'minggu', 'jam' => '08:00:00', 'status' => 'tersedia']);
        $baru = $this->pesananBaru(tutor: $this->tutor->load('jadwal')->setRelation('jadwal', $this->tutor->jadwal()->where('hari', 'minggu')->get()));
        $this->assertSame('95000.00', $baru->tarif_per_jam);
    }

    public function test_tutor_cannot_change_their_own_subjects(): void
    {
        $sebelum = $this->tutor->mataPelajaran()->pluck('mata_pelajaran.kode_mapel')->all();
        $lain = MataPelajaran::factory()->create();

        $this->actingAs($this->akun)->put(route('tutor.profil.update'), $this->isianProfil(['mapel' => [$lain->kode_mapel], 'jenjang' => ['SMA']]));

        $this->assertSame($sebelum, $this->tutor->mataPelajaran()->pluck('mata_pelajaran.kode_mapel')->all());
        $this->actingAs($this->akun)->get(route('tutor.profil.edit'))
            ->assertSeeText('Hubungi admin untuk mengubah mata pelajaran.')
            ->assertDontSee('name="mapel[]"', false);
    }

    public function test_payout_request_needs_an_account_one_waiting_request_and_enough_balance(): void
    {
        $this->pesananSelesai(tutor: $this->tutor);
        $saldo = app(SaldoTutor::class)->satu($this->tutor->id_tutor)['saldo'];
        $this->assertSame('68000.00', $saldo);

        $isian = ['periode' => now()->format('Y-m'), 'nominal' => '50000'];

        $this->actingAs($this->akun)->post(route('tutor.pendapatan.ajukan'), $isian)
            ->assertSessionHas('galat', 'Isi rekening pencairan dulu sebelum mengajukan pencairan.');

        $this->actingAs($this->akun)->put(route('tutor.pendapatan.rekening'), ['rekening_bank' => 'BRI', 'rekening_nomor' => '0123 4567 8901 4417', 'rekening_atas_nama' => 'Rina Marlina']);
        $this->assertTrue($this->tutor->refresh()->punyaRekening());

        $this->actingAs($this->akun)->post(route('tutor.pendapatan.ajukan'), ['nominal' => '70000'] + $isian)
            ->assertSessionHas('galat', fn (string $pesan) => str_starts_with($pesan, 'Nominal melebihi saldo tersedia'));

        $this->actingAs($this->akun)->post(route('tutor.pendapatan.ajukan'), $isian)->assertSessionHas('status');
        $this->assertSame(StatusPencairan::Menunggu, $this->tutor->pencairan()->sole()->status);

        $this->actingAs($this->akun)->post(route('tutor.pendapatan.ajukan'), ['nominal' => '10000'] + $isian)
            ->assertSessionHas('galat', fn (string $pesan) => str_starts_with($pesan, 'Masih ada pengajuan'));
        $this->assertSame(1, $this->tutor->pencairan()->count());
    }

    public function test_income_page_shows_the_breakdown_and_masks_the_account_number(): void
    {
        $pesanan = $this->pesananSelesai(tutor: $this->tutor);
        $this->tutor->forceFill(['rekening_bank' => 'BRI', 'rekening_nomor' => '0123456789014417', 'rekening_atas_nama' => 'Rina Marlina'])->save();

        $this->actingAs($this->akun)->get(route('tutor.pendapatan.index'))
            ->assertSeeText($pesanan->id_pesanan)
            ->assertSeeText('Rp80.000')
            ->assertSeeText('Rp12.000')
            ->assertSeeText('Rp68.000')
            ->assertSeeText('••••4417')
            ->assertDontSeeText('0123456789014417');

        $this->tutor->pencairan()->create(['periode' => now()->format('Y-m'), 'nominal' => 10000]);
        $this->actingAs(AkunPengguna::factory()->admin()->create())->get(route('admin.pencairan.index'))->assertSeeText('0123456789014417');
    }

    public function test_tutor_files_a_complaint_and_admin_sees_who_filed_it(): void
    {
        $pesanan = $this->pesananDibayar(tutor: $this->tutor);

        $this->actingAs($this->akun)->post(route('tutor.pesanan.keluhan', $pesanan), ['jenis' => 'lainnya', 'deskripsi' => 'Siswa pindah kota, perlu dibatalkan.'])
            ->assertRedirect(route('tutor.pesanan.show', $pesanan));

        $keluhan = $pesanan->keluhan()->sole();
        $this->assertSame($this->akun->id_akun, $keluhan->id_akun);

        $admin = AkunPengguna::factory()->admin()->create();
        $this->actingAs($admin)->get(route('admin.keluhan.index'))->assertSeeTextInOrder([$this->akun->nama, 'Tutor']);
        $this->actingAs($admin)->put(route('admin.keluhan.update', $keluhan), ['status' => 'diproses', 'tanggapan_admin' => 'Kami hubungi orang tua dulu.']);

        $this->actingAs($this->akun)->get(route('tutor.keluhan.index'))->assertSeeText('Kami hubungi orang tua dulu.');
    }

    public function test_reviews_page_lists_only_shown_reviews_of_this_tutor(): void
    {
        $tampil = $this->pesananSelesai(tutor: $this->tutor);
        $tampil->ulasan()->create(['nilai' => 5, 'komentar' => 'Sabar sekali mengajarnya.']);

        $this->actingAs($this->akun)->get(route('tutor.ulasan.index'))->assertSeeText('Sabar sekali mengajarnya.')->assertSeeText('5,0');
    }

    #[TestWith(['ortu'])]
    #[TestWith(['admin'])]
    public function test_parents_and_admins_cannot_open_the_tutor_dashboard(string $peran): void
    {
        $akun = AkunPengguna::factory()->{$peran}()->create();
        $pesanan = $this->pesananBaru(tutor: $this->tutor);

        $this->actingAs($akun)->get(route('tutor.area'))->assertForbidden();
        $this->actingAs($akun)->get(route('tutor.pesanan.show', $pesanan))->assertForbidden();
        $this->actingAs($akun)->post(route('tutor.pesanan.terima', $pesanan))->assertForbidden();
        $this->assertInstanceOf(Pesanan::class, $pesanan);
    }

    public function test_an_unverified_tutor_cannot_open_the_dashboard(): void
    {
        $draf = Tutor::factory()->draf()->siapDipesan()->denganAkun()->create();

        $this->actingAs($draf->akun)->get(route('tutor.pesanan.index'))->assertRedirect();
        $this->actingAs($draf->akun)->get(route('tutor.pendapatan.index'))->assertRedirect();
    }
}
