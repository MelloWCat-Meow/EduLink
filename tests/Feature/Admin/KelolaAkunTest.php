<?php

namespace Tests\Feature\Admin;

use App\Enums\JenisNotifikasi;
use App\Enums\StatusJadwal;
use App\Enums\StatusPesanan;
use App\Http\Middleware\PastikanAkunAktif;
use App\Models\AkunPengguna;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Notifications\Pemberitahuan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class KelolaAkunTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    private AkunPengguna $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->admin = AkunPengguna::factory()->admin()->create();
    }

    private function nonaktifkan(AkunPengguna $akun, string $alasan = 'Melanggar syarat: transaksi di luar platform.'): void
    {
        $this->actingAs($this->admin)->post(route('admin.pengguna.nonaktifkan', $akun), ['alasan' => $alasan])->assertRedirect(route('admin.pengguna.show', $akun));
    }

    public function test_a_switched_off_account_cannot_sign_in(): void
    {
        $ortu = AkunPengguna::factory()->ortu()->create(['kontak' => 'siti@contoh.com']);
        $this->nonaktifkan($ortu);
        $this->post(route('logout'));

        $this->post(route('login.store'), ['kontak' => 'siti@contoh.com', 'password' => 'password'])
            ->assertSessionHasErrors(['kontak' => PastikanAkunAktif::PESAN]);
        $this->assertGuest();

        $ortu->refresh();
        $this->assertFalse($ortu->aktif);
        $this->assertNotNull($ortu->dinonaktifkan_pada);
        $this->assertDatabaseHas('riwayat_status_akun', ['id_akun' => $ortu->id_akun, 'aktif' => false, 'oleh' => $this->admin->id_akun, 'alasan' => 'Melanggar syarat: transaksi di luar platform.']);
    }

    public function test_a_running_session_is_ended_on_the_next_request(): void
    {
        $ortu = AkunPengguna::factory()->ortu()->create();
        $ortu->forceFill(['aktif' => false])->save();

        $this->actingAs($ortu->refresh())->get(route('beranda'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['kontak' => PastikanAkunAktif::PESAN]);
        $this->assertGuest();
    }

    public function test_switching_off_a_tutor_cancels_waiting_orders_frees_slots_hides_the_profile_and_keeps_paid_orders(): void
    {
        $tutor = Tutor::factory()->siapDipesan()->denganAkun()->create(['nama' => 'Tutor Bermasalah'])->load('profil', 'mataPelajaran', 'jadwal');
        $menunggu = $this->pesananBaru(null, $tutor);
        $tutor->jadwal()->create(['hari' => 'minggu', 'jam' => '08:00:00', 'status' => 'tersedia']);
        $dibayar = $this->pesananDibayar(null, $tutor->setRelation('jadwal', $tutor->jadwal()->where('hari', 'minggu')->get()));
        $ortuMenunggu = $menunggu->siswa->wali;

        $this->actingAs($this->admin)->get(route('admin.tutor.show', $tutor))->assertOk();
        $this->nonaktifkan($tutor->akun);

        $this->assertSame(StatusPesanan::Batal, $menunggu->refresh()->status);
        $this->assertSame(StatusJadwal::Tersedia, $menunggu->jadwal()->first()->status);
        $this->assertSame(StatusPesanan::Dibayar, $dibayar->refresh()->status);
        Notification::assertSentTo($ortuMenunggu, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::PesananDibatalkan && str_contains($n->pesan, 'akun tutor dinonaktifkan'));

        $this->assertFalse($tutor->isListed());
        $this->get(route('tutor.index'))->assertDontSeeText('Tutor Bermasalah');
        $this->actingAs($this->admin)->get(route('admin.pengguna.show', $tutor->akun))
            ->assertSeeText('1 pesanan sudah dibayar dan masih berjalan.')
            ->assertSeeText($dibayar->id_pesanan);
    }

    public function test_switching_off_a_parent_cancels_their_waiting_orders_and_tells_the_tutor(): void
    {
        $tutor = Tutor::factory()->siapDipesan()->denganAkun()->create()->load('profil', 'mataPelajaran', 'jadwal');
        $siswa = Siswa::factory()->create();
        $pesanan = $this->pesananMenungguPembayaran($siswa, $tutor);

        $this->nonaktifkan($siswa->wali);

        $this->assertSame(StatusPesanan::Batal, $pesanan->refresh()->status);
        $this->assertSame(StatusJadwal::Tersedia, $pesanan->jadwal()->first()->status);
        Notification::assertSentTo($tutor->akun, Pemberitahuan::class, fn (Pemberitahuan $n) => str_contains($n->pesan, 'akun orang tua dinonaktifkan'));
    }

    public function test_switching_back_on_needs_a_reason_and_allows_sign_in_again(): void
    {
        $ortu = AkunPengguna::factory()->ortu()->create(['kontak' => 'budi@contoh.com']);
        $this->nonaktifkan($ortu);

        $this->actingAs($this->admin)->post(route('admin.pengguna.aktifkan', $ortu), ['alasan' => ''])->assertSessionHasErrors('alasan');
        $this->actingAs($this->admin)->post(route('admin.pengguna.aktifkan', $ortu), ['alasan' => 'Sudah klarifikasi dengan orang tua.']);
        $this->post(route('logout'));

        $this->assertTrue($ortu->refresh()->aktif);
        $this->post(route('login.store'), ['kontak' => 'budi@contoh.com', 'password' => 'password'])->assertRedirect(route('beranda'));
        $this->actingAs($this->admin)->get(route('admin.pengguna.show', $ortu))->assertSeeText('Diaktifkan')->assertSeeText('Sudah klarifikasi');
    }

    public function test_admin_accounts_cannot_be_switched_off(): void
    {
        $adminLain = AkunPengguna::factory()->admin()->create();

        $this->actingAs($this->admin)->post(route('admin.pengguna.nonaktifkan', $adminLain), ['alasan' => 'Mencoba menonaktifkan admin.'])->assertSessionHas('galat');
        $this->assertTrue($adminLain->refresh()->aktif);
        $this->actingAs($this->admin)->get(route('admin.pengguna.show', $adminLain))->assertNotFound();
        $this->actingAs($this->admin)->get(route('admin.pengguna.index'))->assertDontSeeText($adminLain->kontak);
    }

    public function test_only_admins_can_switch_accounts(): void
    {
        $ortu = AkunPengguna::factory()->ortu()->create();

        $this->actingAs(AkunPengguna::factory()->ortu()->create())->post(route('admin.pengguna.nonaktifkan', $ortu), ['alasan' => 'Iseng saja ya.'])->assertForbidden();
        $this->assertTrue($ortu->refresh()->aktif);
    }

    public function test_admin_changes_a_tutors_subjects_with_a_reason(): void
    {
        $tutor = Tutor::factory()->siapDipesan()->denganAkun()->create()->load('mataPelajaran');
        $lama = $tutor->mataPelajaran->first();
        $baru = MataPelajaran::factory()->create(['nama' => 'Kimia', 'jenjang' => 'SMA']);

        $this->actingAs($this->admin)->put(route('admin.tutor.mapel', $tutor), ['mapel' => [$baru->kode_mapel]])->assertSessionHasErrors('alasan');

        $this->actingAs($this->admin)->put(route('admin.tutor.mapel', $tutor), ['mapel' => [$lama->kode_mapel, $baru->kode_mapel], 'alasan' => 'Sertifikat kimia sudah diperiksa.'])
            ->assertRedirect(route('admin.tutor.show', $tutor));

        $this->assertEqualsCanonicalizing([$lama->kode_mapel, $baru->kode_mapel], $tutor->mataPelajaran()->pluck('mata_pelajaran.kode_mapel')->all());
        $this->assertTrue($tutor->riwayatVerifikasi()->where('catatan', 'like', '%Ditambah: Kimia SMA.%Alasan: Sertifikat kimia sudah diperiksa.%')->exists());
        Notification::assertSentTo($tutor->akun, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::MapelDiubah);
    }

    public function test_a_subject_used_by_a_running_order_cannot_be_removed(): void
    {
        $tutor = Tutor::factory()->siapDipesan()->denganAkun()->create()->load('profil', 'mataPelajaran', 'jadwal');
        $this->pesananDibayar(null, $tutor);
        $dipakai = $tutor->mataPelajaran->first();
        $lain = MataPelajaran::factory()->create();

        $this->actingAs($this->admin)->put(route('admin.tutor.mapel', $tutor), ['mapel' => [$lain->kode_mapel], 'alasan' => 'Fokus ke mapel lain saja.'])
            ->assertSessionHas('galat', fn (string $pesan) => str_contains($pesan, 'masih dipakai pesanan'));

        $this->assertSame([$dipakai->kode_mapel], $tutor->mataPelajaran()->pluck('mata_pelajaran.kode_mapel')->all());
    }

    public function test_tutor_list_filters_by_status_kind_activity_and_name(): void
    {
        Tutor::factory()->internal()->create(['nama' => 'Internal Aktif']);
        $nonaktif = Tutor::factory()->eksternal()->denganAkun()->create(['nama' => 'Eksternal Nonaktif']);
        $nonaktif->akun->forceFill(['aktif' => false])->save();
        Tutor::factory()->eksternal()->menunggu()->create(['nama' => 'Eksternal Menunggu']);

        $this->actingAs($this->admin)->get(route('admin.tutor.index', ['aktif' => 'nonaktif']))->assertSeeText('Eksternal Nonaktif')->assertDontSeeText('Internal Aktif');
        $this->actingAs($this->admin)->get(route('admin.tutor.index', ['jenis' => 'internal']))->assertSeeText('Internal Aktif')->assertDontSeeText('Eksternal Menunggu');
        $this->actingAs($this->admin)->get(route('admin.tutor.index', ['status' => 'menunggu']))->assertSeeText('Eksternal Menunggu')->assertDontSeeText('Internal Aktif');
        $this->actingAs($this->admin)->get(route('admin.tutor.index', ['cari' => 'nonaktif']))->assertSeeText('Eksternal Nonaktif')->assertDontSeeText('Eksternal Menunggu');
    }
}
