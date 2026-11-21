<?php

namespace Tests\Feature;

use App\Console\Commands\SimulasiSesi;
use App\Console\Commands\SimulasiTutor;
use App\Enums\MetodePembayaran;
use App\Models\AkunPengguna;
use App\Models\Tutor;
use App\Services\PemrosesPembayaran;
use App\Support\TandaTanganWebhook;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class ModeDemoTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    public function test_simulations_follow_the_config_not_app_env(): void
    {
        $this->app['env'] = 'production';
        $this->withoutMiddleware(PreventRequestForgery::class);
        config(['edulink.mode_demo' => true]);

        $pesanan = $this->pesananMenungguPembayaran();
        $ortu = $pesanan->siswa->wali;

        $this->actingAs($ortu)->post(route('pesanan.bayar', $pesanan), ['metode' => 'qris'])->assertRedirect();
        $pembayaran = $pesanan->pembayaran()->sole();
        $this->actingAs($ortu)->get(route('simulasi.pembayaran', $pembayaran))->assertOk();
        $this->get(route('landing'))->assertSeeText('pembayaran disimulasikan');

        $this->artisan('list')->expectsOutputToContain('edulink:simulasi-tutor');
    }

    public function test_simulations_are_gone_when_demo_mode_is_off_even_locally(): void
    {
        $this->app['env'] = 'local';
        $this->withoutMiddleware(PreventRequestForgery::class);
        config(['edulink.mode_demo' => false]);

        $pesanan = $this->pesananMenungguPembayaran();
        $ortu = $pesanan->siswa->wali;

        $this->actingAs($ortu)->post(route('pesanan.bayar', $pesanan), ['metode' => 'qris'])->assertStatus(503);
        $this->assertSame(0, $pesanan->pembayaran()->count());

        $this->postJson(route('webhooks.payment'), ['payment_id' => 'PAY0001'])->assertNotFound();
        $this->get(route('landing'))->assertDontSeeText('pembayaran disimulasikan');
        $this->assertFalse((new SimulasiTutor)->isEnabled());
        $this->assertFalse((new SimulasiSesi)->isEnabled());
    }

    public function test_the_webhook_still_checks_the_signature_in_demo_mode(): void
    {
        config(['edulink.mode_demo' => true]);
        $pesanan = $this->pesananMenungguPembayaran();
        $pembayaran = app(PemrosesPembayaran::class)->mulai($pesanan, MetodePembayaran::Qris);
        $isian = ['payment_id' => $pembayaran->id_pembayaran, 'order_id' => $pesanan->id_pesanan, 'status' => 'berhasil'];

        $this->postJson(route('webhooks.payment'), $isian + ['signature' => 'palsu'])->assertStatus(422);
        $this->assertSame('menunggu_pembayaran', $pesanan->refresh()->status->value);

        $this->postJson(route('webhooks.payment'), $isian + ['signature' => TandaTanganWebhook::buat($pembayaran->id_pembayaran, $pesanan->id_pesanan, 'berhasil')])->assertOk();
        $this->assertSame('dibayar', $pesanan->refresh()->status->value);
    }

    public function test_activation_link_is_shown_on_screen_only_in_demo_mode(): void
    {
        Mail::fake();
        Tutor::factory()->internal()->draf()->create(['kontak' => 'lestari@contoh.com']);

        config(['edulink.mode_demo' => false]);
        $this->post(route('tutor.aktivasi.store'), ['kontak' => 'lestari@contoh.com'])->assertSessionMissing('tautan_demo');

        config(['edulink.mode_demo' => true]);
        $this->post(route('tutor.aktivasi.store'), ['kontak' => 'lestari@contoh.com'])->assertSessionHas('tautan_demo');

        $this->assertInstanceOf(AkunPengguna::class, new AkunPengguna);
    }
}
