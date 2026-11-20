<?php

namespace Tests\Feature\Notifikasi;

use App\Enums\JenisNotifikasi;
use App\Models\AkunPengguna;
use App\Models\Tutor;
use App\Notifications\Pemberitahuan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class HalamanNotifikasiTest extends TestCase
{
    use RefreshDatabase;

    private function beri(AkunPengguna $akun, string $judul, ?string $tautan = null): void
    {
        $akun->notify(new Pemberitahuan(JenisNotifikasi::UlasanBaru, $judul, 'Isi '.$judul, $tautan ?? route('notifikasi.index')));
    }

    #[TestWith(['ortu', 'beranda'])]
    #[TestWith(['admin', 'admin.beranda'])]
    public function test_the_bell_shows_the_unread_count_on_every_dashboard(string $peran, string $beranda): void
    {
        $akun = AkunPengguna::factory()->{$peran}()->create();
        $this->beri($akun, 'Satu');
        $this->beri($akun, 'Dua');

        $this->actingAs($akun)->get(route($beranda))->assertSee(route('notifikasi.index'), false)->assertSeeText('Notifikasi, 2 belum dibaca');
    }

    public function test_the_bell_also_shows_on_the_tutor_dashboard(): void
    {
        $tutor = Tutor::factory()->siapDipesan()->denganAkun()->create();
        $this->beri($tutor->akun, 'Satu');

        $this->actingAs($tutor->akun)->get(route('tutor.area'))->assertSeeText('Notifikasi, 1 belum dibaca');
        $this->actingAs($tutor->akun)->get(route('notifikasi.index'))->assertOk()->assertSeeText('Isi Satu');
    }

    public function test_opening_a_notification_marks_it_read_and_goes_to_its_page(): void
    {
        $ortu = AkunPengguna::factory()->ortu()->create();
        $this->beri($ortu, 'Pesanan diterima', route('pesanan.index'));
        $notifikasi = $ortu->notifications()->sole();

        $this->actingAs($ortu)->get(route('notifikasi.index'))->assertSeeText('Pesanan diterima')->assertSeeText('Baru');
        $this->actingAs($ortu)->post(route('notifikasi.buka', $notifikasi->id))->assertRedirect(route('pesanan.index'));

        $this->assertNotNull($notifikasi->refresh()->read_at);
        $this->actingAs($ortu)->get(route('beranda'))->assertDontSeeText('belum dibaca');
    }

    public function test_mark_all_as_read(): void
    {
        $ortu = AkunPengguna::factory()->ortu()->create();
        $this->beri($ortu, 'Satu');
        $this->beri($ortu, 'Dua');

        $this->actingAs($ortu)->post(route('notifikasi.baca-semua'))->assertRedirect(route('notifikasi.index'));

        $this->assertSame(0, $ortu->unreadNotifications()->count());
    }

    public function test_nobody_can_open_someone_elses_notification(): void
    {
        $pemilik = AkunPengguna::factory()->ortu()->create();
        $lain = AkunPengguna::factory()->ortu()->create();
        $this->beri($pemilik, 'Rahasia');

        $this->actingAs($lain)->post(route('notifikasi.buka', $pemilik->notifications()->sole()->id))->assertNotFound();
        $this->actingAs($lain)->get(route('notifikasi.index'))->assertDontSeeText('Rahasia');
        $this->assertNull($pemilik->notifications()->sole()->read_at);
    }

    public function test_guests_are_sent_to_sign_in(): void
    {
        $this->get(route('notifikasi.index'))->assertRedirect(route('login'));
    }

    public function test_important_kinds_also_email_the_sign_in_address(): void
    {
        Mail::fake();
        $ortu = AkunPengguna::factory()->ortu()->create(['kontak' => 'siti@contoh.com']);

        $ortu->notify(new Pemberitahuan(JenisNotifikasi::PesananDiterima, 'Pesanan diterima', 'Silakan bayar.', route('pesanan.index')));
        $mail = (new Pemberitahuan(JenisNotifikasi::PesananDiterima, 'Pesanan diterima', 'Silakan bayar.', route('pesanan.index')))->toMail($ortu);

        $this->assertSame(['siti@contoh.com' => $ortu->nama], $ortu->routeNotificationForMail());
        $this->assertSame('Pesanan diterima', $mail->subject);
        $this->assertSame(1, $ortu->notifications()->count());
    }
}
