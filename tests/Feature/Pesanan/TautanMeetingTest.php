<?php

namespace Tests\Feature\Pesanan;

use App\Enums\JenisNotifikasi;
use App\Models\AkunPengguna;
use App\Models\Pesanan;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Notifications\Pemberitahuan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class TautanMeetingTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    private Tutor $tutor;

    private AkunPengguna $ortu;

    private Pesanan $pesanan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tutor = Tutor::factory()->siapDipesan()->denganAkun()->create()->load('profil', 'mataPelajaran', 'jadwal');
        $siswa = Siswa::factory()->create();
        $this->ortu = $siswa->wali;
        $this->pesanan = $this->pesananDibayar($siswa, $this->tutor, ['jumlah_pertemuan' => 2]);
    }

    public function test_paid_online_orders_get_no_generated_link(): void
    {
        $this->assertNull($this->pesanan->tautan_meeting);
        $this->assertSame(0, $this->pesanan->sesi()->whereNotNull('tautan_meeting')->count());

        $this->actingAs($this->ortu)->get(route('pesanan.show', $this->pesanan))
            ->assertSeeText('Tutor belum membagikan tautan meeting.')
            ->assertSeeText('Chat tutor via WhatsApp');
    }

    public function test_the_tutor_shares_a_default_link_that_every_session_uses(): void
    {
        $this->actingAs($this->tutor->akun)->put(route('tutor.pesanan.tautan', $this->pesanan), ['tautan_meeting' => 'https://meet.google.com/abc-defg-hij'])
            ->assertSessionHas('status');

        $this->assertSame('https://meet.google.com/abc-defg-hij', $this->pesanan->refresh()->tautan_meeting);
        $this->actingAs($this->ortu)->get(route('pesanan.show', $this->pesanan))
            ->assertSee('href="https://meet.google.com/abc-defg-hij"', false)
            ->assertDontSeeText('Tutor belum membagikan tautan meeting.');
    }

    public function test_a_session_link_overrides_the_default_and_clearing_it_falls_back(): void
    {
        $this->pesanan->forceFill(['tautan_meeting' => 'https://meet.google.com/default'])->save();
        [$pertama, $kedua] = $this->pesanan->sesi()->orderBy('waktu')->get();

        $this->actingAs($this->tutor->akun)->put(route('tutor.sesi.tautan', $kedua), ['tautan_meeting' => 'https://zoom.us/j/123456789']);

        $pesanan = $this->pesanan->refresh();
        $this->assertSame('https://meet.google.com/default', $pertama->refresh()->tautanBerlaku($pesanan));
        $this->assertSame('https://zoom.us/j/123456789', $kedua->refresh()->tautanBerlaku($pesanan));
        $this->actingAs($this->ortu)->get(route('pesanan.show', $pesanan))->assertSee('href="https://zoom.us/j/123456789"', false)->assertSee('href="https://meet.google.com/default"', false);

        $this->actingAs($this->tutor->akun)->put(route('tutor.sesi.tautan', $kedua), ['tautan_meeting' => '']);
        $this->assertSame('https://meet.google.com/default', $kedua->refresh()->tautanBerlaku($pesanan));
    }

    #[TestWith(['http://meet.google.com/abc'])]
    #[TestWith(['meet.google.com/abc'])]
    #[TestWith(['javascript:alert(1)'])]
    public function test_only_https_links_are_accepted(string $tautan): void
    {
        $this->actingAs($this->tutor->akun)->put(route('tutor.pesanan.tautan', $this->pesanan), ['tautan_meeting' => $tautan])->assertSessionHasErrors('tautan_meeting');
        $this->actingAs($this->tutor->akun)->put(route('tutor.pesanan.tautan', $this->pesanan), ['tautan_meeting' => 'https://zoom.us/j/'.str_repeat('1', 250)])->assertSessionHasErrors('tautan_meeting');

        $this->assertNull($this->pesanan->refresh()->tautan_meeting);
    }

    public function test_only_the_tutor_of_the_order_can_set_links(): void
    {
        $sesi = $this->pesanan->sesi()->first();
        $isian = ['tautan_meeting' => 'https://meet.google.com/penyusup'];

        $this->actingAs(Tutor::factory()->denganAkun()->create()->akun)->put(route('tutor.pesanan.tautan', $this->pesanan), $isian)->assertForbidden();
        $this->actingAs(Tutor::factory()->denganAkun()->create()->akun)->put(route('tutor.sesi.tautan', $sesi), $isian)->assertForbidden();
        $this->actingAs($this->ortu)->put(route('tutor.pesanan.tautan', $this->pesanan), $isian)->assertForbidden();

        $this->assertNull($this->pesanan->refresh()->tautan_meeting);
        $this->assertNull($sesi->refresh()->tautan_meeting);
    }

    public function test_links_are_only_for_paid_online_orders_and_only_shown_when_paid(): void
    {
        $belumBayar = $this->pesananMenungguPembayaran(null, $this->tutor->setRelation('jadwal', collect([$this->tutor->jadwal()->create(['hari' => 'minggu', 'jam' => '08:00:00', 'status' => 'tersedia'])])));
        $this->actingAs($this->tutor->akun)->put(route('tutor.pesanan.tautan', $belumBayar), ['tautan_meeting' => 'https://meet.google.com/awal'])
            ->assertSessionHas('galat', 'Tautan meeting bisa dibagikan setelah pesanan dibayar.');

        $this->pesanan->forceFill(['tautan_meeting' => 'https://meet.google.com/rahasia', 'mode' => 'kunjungan_rumah'])->save();
        $this->actingAs($this->tutor->akun)->put(route('tutor.pesanan.tautan', $this->pesanan), ['tautan_meeting' => 'https://meet.google.com/lain'])
            ->assertSessionHas('galat', 'Tautan meeting hanya untuk pesanan online.');

        // A link left on an unpaid or cancelled order is never shown.
        $belumBayar->forceFill(['tautan_meeting' => 'https://meet.google.com/bocor'])->save();
        $this->actingAs($belumBayar->siswa->wali)->get(route('pesanan.show', $belumBayar))->assertDontSee('meet.google.com/bocor', false);
    }

    public function test_tomorrows_reminder_tells_the_tutor_when_the_link_is_missing(): void
    {
        Notification::fake();
        $sesi = $this->pesanan->sesi()->orderBy('waktu')->first();
        $this->travelTo($sesi->waktu->copy()->subDay()->setTime(7, 0));

        $this->artisan('edulink:pengingat-sesi');

        Notification::assertSentTo($this->tutor->akun, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::PengingatSesi && str_contains($n->pesan, 'Tautan meeting sesi ini belum diisi'));
        Notification::assertSentTo($this->ortu, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::PengingatSesi && str_contains($n->pesan, 'Tutor belum membagikan tautan meeting'));
    }
}
