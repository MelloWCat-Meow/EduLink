<?php

namespace Tests\Feature\Pesanan;

use App\Models\AkunPengguna;
use App\Models\Pesanan;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Services\AlurPesanan;
use App\Services\KontakWhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class WhatsAppTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    private Tutor $tutor;

    private Siswa $siswa;

    private AkunPengguna $ortu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tutor = Tutor::factory()->siapDipesan()->denganAkun()->create(['nama' => 'Rina Marlina', 'no_hp' => '0812-6610 0201'])->load('profil', 'mataPelajaran', 'jadwal');
        $this->ortu = AkunPengguna::factory()->ortu()->create(['nama' => 'Siti Aminah', 'no_hp' => '+62 813 7720 0101']);
        $this->siswa = Siswa::factory()->for($this->ortu, 'wali')->create(['nama' => 'Nadia Putri']);
    }

    public function test_parent_of_a_paid_order_gets_a_prefilled_chat_with_the_tutor(): void
    {
        $pesanan = $this->pesananDibayar($this->siswa, $this->tutor);
        $pesan = rawurlencode("Halo Kak Rina Marlina, saya Siti Aminah, wali dari Nadia Putri, untuk pesanan {$pesanan->id_pesanan} ({$pesanan->mapel->label()}).");

        $this->actingAs($this->ortu)->get(route('pesanan.show', $pesanan))
            ->assertSeeText('Chat tutor via WhatsApp')
            ->assertSee('href="https://wa.me/6281266100201?text='.$pesan.'" target="_blank" rel="noopener"', false);
    }

    public function test_tutor_of_a_paid_order_gets_a_prefilled_chat_with_the_parent(): void
    {
        $pesanan = $this->pesananDibayar($this->siswa, $this->tutor);

        $this->actingAs($this->tutor->akun)->get(route('tutor.pesanan.show', $pesanan))
            ->assertSeeText('Chat orang tua via WhatsApp')
            ->assertSee('https://wa.me/6281377200101?text='.rawurlencode('Halo Bapak/Ibu Siti Aminah, saya Rina Marlina'), false);
    }

    #[TestWith(['pesananBaru'])]
    #[TestWith(['pesananMenungguPembayaran'])]
    public function test_no_link_and_no_number_before_payment(string $buat): void
    {
        $pesanan = $this->{$buat}($this->siswa, $this->tutor);

        foreach ([[$this->ortu, route('pesanan.show', $pesanan)], [$this->tutor->akun, route('tutor.pesanan.show', $pesanan)]] as [$akun, $url]) {
            $this->actingAs($akun)->get($url)
                ->assertSeeText('Tombol WhatsApp muncul setelah pesanan dibayar.')
                ->assertDontSee('wa.me', false)
                ->assertDontSee('6281266100201', false)
                ->assertDontSee('6281377200101', false)
                ->assertDontSee('0812-6610', false);
        }
    }

    public function test_cancelled_or_rejected_orders_get_no_link(): void
    {
        $ditolak = $this->pesananBaru($this->siswa, $this->tutor);
        app(AlurPesanan::class)->tolakOlehTutor($ditolak, 'Penuh.', $this->tutor->akun);

        $this->assertNull(app(KontakWhatsApp::class)->untuk($this->ortu, $ditolak->refresh())['tautan']);
        $this->actingAs($this->ortu)->get(route('pesanan.show', $ditolak))->assertDontSee('wa.me', false);
    }

    public function test_finished_orders_keep_the_link_for_fourteen_days_only(): void
    {
        $pesanan = $this->pesananSelesai($this->siswa, $this->tutor);

        $this->travel(13)->days();
        $this->actingAs($this->ortu)->get(route('pesanan.show', $pesanan))->assertSee('wa.me/6281266100201', false);

        $this->travel(2)->days();
        $this->actingAs($this->ortu)->get(route('pesanan.show', $pesanan))
            ->assertDontSee('wa.me', false)
            ->assertSeeText('hanya tersedia sampai 14 hari setelah pesanan selesai');
    }

    public function test_a_missing_number_shows_a_note_instead_of_a_broken_button(): void
    {
        $this->tutor->update(['no_hp' => null]);
        $this->ortu->update(['no_hp' => null]);
        $pesanan = $this->pesananDibayar($this->siswa, $this->tutor);

        $this->actingAs($this->ortu)->get(route('pesanan.show', $pesanan))->assertSeeText('Tutor belum mengisi nomor HP.')->assertDontSee('wa.me', false);
        $this->actingAs($this->tutor->akun)->get(route('tutor.pesanan.show', $pesanan))->assertSeeText('Orang tua belum mengisi nomor HP.')->assertDontSee('wa.me', false);
    }

    public function test_the_link_never_reaches_anyone_outside_the_order(): void
    {
        $pesanan = $this->pesananDibayar($this->siswa, $this->tutor);
        $kontak = app(KontakWhatsApp::class);

        $ortuLain = AkunPengguna::factory()->ortu()->create();
        $tutorLain = Tutor::factory()->denganAkun()->create();
        $admin = AkunPengguna::factory()->admin()->create();

        foreach ([$ortuLain, $tutorLain->akun, $admin] as $akun) {
            $this->assertSame(['tautan' => null, 'keterangan' => null], array_intersect_key($kontak->untuk($akun, $pesanan), ['tautan' => 1, 'keterangan' => 1]));
        }

        $this->actingAs($ortuLain)->get(route('pesanan.show', $pesanan))->assertForbidden();
        $this->actingAs($tutorLain->akun)->get(route('tutor.pesanan.show', $pesanan))->assertForbidden();
    }

    public function test_the_tutor_number_never_appears_on_public_pages(): void
    {
        $this->pesananDibayar($this->siswa, $this->tutor);

        foreach ([route('tutor.index'), route('tutor.show', $this->tutor), route('landing')] as $url) {
            $this->get($url)->assertDontSee('6281266100201', false)->assertDontSee('0812-6610', false)->assertDontSee('wa.me', false);
            $this->actingAs($this->ortu)->get($url)->assertDontSee('6281266100201', false)->assertDontSee('wa.me', false);
            $this->post(route('logout'));
        }

        $this->assertInstanceOf(Pesanan::class, Pesanan::query()->first());
    }
}
