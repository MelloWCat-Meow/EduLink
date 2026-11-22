<?php

namespace Tests\Feature\Ortu;

use App\Enums\StatusPesanan;
use App\Models\AkunPengguna;
use App\Models\Keluhan;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\Siswa;
use App\Support\TandaTanganWebhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class PesananOrtuTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    private function wali(Pesanan $pesanan): AkunPengguna
    {
        return AkunPengguna::query()->findOrFail(Siswa::query()->whereKey($pesanan->id_siswa)->value('id_akun'));
    }

    public function test_order_list_shows_own_orders_and_filters_by_status(): void
    {
        $siswa = Siswa::factory()->create();
        $menunggu = $this->pesananBaru($siswa);
        $dibayar = $this->pesananDibayar($siswa);
        $milikLain = $this->pesananBaru();
        $ortu = $this->wali($menunggu);

        $this->actingAs($ortu)->get(route('pesanan.index'))
            ->assertOk()
            ->assertSeeText($menunggu->id_pesanan)
            ->assertSeeText($dibayar->id_pesanan)
            ->assertDontSeeText($milikLain->id_pesanan);

        $this->actingAs($ortu)->get(route('pesanan.index', ['status' => 'dibayar']))
            ->assertSeeText($dibayar->id_pesanan)
            ->assertDontSeeText($menunggu->id_pesanan);
    }

    public function test_order_list_has_an_empty_state(): void
    {
        $this->actingAs(AkunPengguna::factory()->ortu()->create())->get(route('pesanan.index'))
            ->assertSeeText('Anda belum pernah memesan tutor.');
    }

    public function test_order_detail_shows_timeline_costs_and_sessions(): void
    {
        $pesanan = $this->pesananDibayar();

        $this->actingAs($this->wali($pesanan))->get(route('pesanan.show', $pesanan))
            ->assertOk()
            ->assertSeeText('Riwayat status')
            ->assertSeeText('Tutor menerima pesanan.')
            ->assertSeeText('Rp300.000')
            ->assertSeeText('Pertemuan 4')
            ->assertSeeText('Tutor belum membagikan tautan meeting.');
    }

    #[TestWith(['get', 'pesanan.show'])]
    #[TestWith(['post', 'pesanan.batal'])]
    #[TestWith(['post', 'pesanan.bayar'])]
    #[TestWith(['post', 'pesanan.ulasan'])]
    #[TestWith(['post', 'pesanan.keluhan'])]
    public function test_parent_cannot_reach_another_accounts_order(string $method, string $route): void
    {
        $milikLain = $this->pesananMenungguPembayaran();
        $ortu = AkunPengguna::factory()->ortu()->create();

        $this->actingAs($ortu)->{$method}(route($route, $milikLain), ['metode' => 'qris', 'nilai' => 5, 'jenis' => 'lainnya', 'deskripsi' => 'Coba akses data orang lain.'])
            ->assertForbidden();

        $this->assertSame(StatusPesanan::MenungguPembayaran, $milikLain->refresh()->status);
        $this->assertDatabaseCount('keluhan', 0);
    }

    public function test_parent_cancels_an_unpaid_order(): void
    {
        $pesanan = $this->pesananBaru();

        $this->actingAs($this->wali($pesanan))->post(route('pesanan.batal', $pesanan))->assertRedirect(route('pesanan.show', $pesanan));

        $this->assertSame(StatusPesanan::Batal, $pesanan->refresh()->status);
    }

    public function test_cancelling_a_paid_order_is_refused_with_a_message(): void
    {
        $pesanan = $this->pesananDibayar();

        $this->actingAs($this->wali($pesanan))->from(route('pesanan.show', $pesanan))->post(route('pesanan.batal', $pesanan))
            ->assertRedirect(route('pesanan.show', $pesanan))
            ->assertSessionHas('galat');

        $this->assertSame(StatusPesanan::Dibayar, $pesanan->refresh()->status);
    }

    public function test_paying_goes_through_the_simulator_and_the_webhook(): void
    {
        $pesanan = $this->pesananMenungguPembayaran();
        $ortu = $this->wali($pesanan);

        $response = $this->actingAs($ortu)->post(route('pesanan.bayar', $pesanan), ['metode' => 'virtual_account']);
        $pembayaran = Pembayaran::query()->sole();
        $response->assertRedirect(route('simulasi.pembayaran', $pembayaran));

        $this->actingAs($ortu)->get(route('simulasi.pembayaran', $pembayaran))->assertOk()->assertSeeText('Pembayaran berhasil');

        $this->post(route('webhooks.payment'), [
            'payment_id' => $pembayaran->id_pembayaran,
            'order_id' => $pesanan->id_pesanan,
            'status' => 'berhasil',
            'signature' => TandaTanganWebhook::buat($pembayaran->id_pembayaran, $pesanan->id_pesanan, 'berhasil'),
        ])->assertRedirect(route('pesanan.show', $pesanan));

        $this->assertSame(StatusPesanan::Dibayar, $pesanan->refresh()->status);
    }

    public function test_parent_cannot_open_the_simulator_for_another_accounts_payment(): void
    {
        $pesanan = $this->pesananMenungguPembayaran();
        $this->actingAs($this->wali($pesanan))->post(route('pesanan.bayar', $pesanan), ['metode' => 'qris']);

        $this->actingAs(AkunPengguna::factory()->ortu()->create())
            ->get(route('simulasi.pembayaran', Pembayaran::query()->sole()))
            ->assertForbidden();
    }

    public function test_review_is_refused_before_the_order_is_finished(): void
    {
        $pesanan = $this->pesananDibayar();

        $this->actingAs($this->wali($pesanan))->post(route('pesanan.ulasan', $pesanan), ['nilai' => 5])
            ->assertSessionHasErrors(['nilai' => 'Ulasan baru bisa diberikan setelah pesanan selesai.']);

        $this->assertDatabaseCount('rating_ulasan', 0);
    }

    public function test_review_is_accepted_once_after_the_order_is_finished(): void
    {
        $pesanan = $this->pesananSelesai();
        $ortu = $this->wali($pesanan);

        $this->actingAs($ortu)->post(route('pesanan.ulasan', $pesanan), ['nilai' => 4, 'komentar' => 'Sabar.'])
            ->assertRedirect(route('pesanan.show', $pesanan));

        $this->actingAs($ortu)->post(route('pesanan.ulasan', $pesanan), ['nilai' => 1])
            ->assertSessionHasErrors(['nilai' => 'Pesanan ini sudah Anda ulas. Ulasan hanya bisa diberikan sekali.']);

        $this->assertSame(4, $pesanan->ulasan()->sole()->nilai);
    }

    public function test_review_score_must_be_between_one_and_five(): void
    {
        $pesanan = $this->pesananSelesai();

        $this->actingAs($this->wali($pesanan))->post(route('pesanan.ulasan', $pesanan), ['nilai' => 6])
            ->assertSessionHasErrors(['nilai' => 'Pilih nilai 1 sampai 5.']);
    }

    public function test_complaint_is_filed_and_the_admin_reply_shows_on_the_complaints_page(): void
    {
        $pesanan = $this->pesananDibayar();
        $ortu = $this->wali($pesanan);

        $this->actingAs($ortu)->post(route('pesanan.keluhan', $pesanan), ['jenis' => 'tutor_tidak_hadir', 'deskripsi' => 'Tutor terlambat 30 menit.'])
            ->assertRedirect(route('pesanan.show', $pesanan));

        $keluhan = Keluhan::query()->sole();
        $this->assertSame($ortu->id_akun, $keluhan->id_akun);
        $keluhan->forceFill(['status' => 'diproses', 'tanggapan_admin' => 'Kami sedang menghubungi tutor.'])->save();

        $this->actingAs($ortu)->get(route('keluhan.index'))
            ->assertSeeText('Tutor terlambat 30 menit.')
            ->assertSeeText('Diproses')
            ->assertSeeText('Kami sedang menghubungi tutor.');
    }

    public function test_complaint_needs_a_description(): void
    {
        $pesanan = $this->pesananDibayar();

        $this->actingAs($this->wali($pesanan))->post(route('pesanan.keluhan', $pesanan), ['jenis' => 'lainnya', 'deskripsi' => ''])
            ->assertSessionHasErrors(['deskripsi' => 'Ceritakan apa yang terjadi.']);
    }

    public function test_complaints_and_payments_pages_list_only_own_records(): void
    {
        $milikku = $this->pesananDibayar();
        $milikLain = $this->pesananDibayar();
        $ortu = $this->wali($milikku);
        $lain = $this->wali($milikLain);
        $keluhanLain = new Keluhan(['id_pesanan' => $milikLain->id_pesanan, 'jenis' => 'lainnya', 'deskripsi' => 'Keluhan milik akun lain.']);
        $keluhanLain->id_akun = $lain->id_akun;
        $keluhanLain->save();

        $this->actingAs($ortu)->get(route('pembayaran.index'))
            ->assertSeeText($milikku->id_pesanan)
            ->assertDontSeeText($milikLain->id_pesanan);

        $this->actingAs($ortu)->get(route('keluhan.index'))
            ->assertDontSeeText('Keluhan milik akun lain.')
            ->assertSeeText('Belum ada keluhan.');
    }

    public function test_home_lists_what_needs_doing(): void
    {
        $siswa = Siswa::factory()->create(['nama' => 'Nadia']);
        $bayar = $this->pesananMenungguPembayaran($siswa);
        $this->pesananDibayar($siswa);
        $selesai = $this->pesananSelesai($siswa);

        $this->actingAs($this->wali($bayar))->get(route('beranda'))
            ->assertOk()
            ->assertSeeText('Bayar '.$bayar->mapel()->first()->label().' untuk Nadia')
            ->assertSeeText('jam')
            ->assertSeeText('Sesi terdekat')
            ->assertSeeText('Beri ulasan untuk '.$selesai->tutor()->value('nama'));
    }

    public function test_home_has_an_empty_to_do_list_for_a_new_parent(): void
    {
        $this->actingAs(AkunPengguna::factory()->ortu()->create())->get(route('beranda'))
            ->assertSeeText('Tambahkan data anak')
            ->assertSeeText('Belum ada pesanan yang berjalan.');
    }

    public function test_parent_is_refused_on_the_admin_pages(): void
    {
        $this->actingAs(AkunPengguna::factory()->ortu()->create())->get(route('admin.beranda'))->assertForbidden();
    }
}
