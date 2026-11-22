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
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_activation_link_is_shown_on_screen_only_in_demo_mode_with_the_flag_on(): void
    {
        Mail::fake();
        Tutor::factory()->internal()->draf()->create(['kontak' => 'lestari@contoh.com']);

        config(['edulink.mode_demo' => false, 'edulink.tautan_di_layar' => true]);
        $this->post(route('tutor.aktivasi.store'), ['kontak' => 'lestari@contoh.com'])->assertSessionMissing('tautan_demo');

        config(['edulink.mode_demo' => true, 'edulink.tautan_di_layar' => false]);
        $this->post(route('tutor.aktivasi.store'), ['kontak' => 'lestari@contoh.com'])->assertSessionMissing('tautan_demo');
        $this->get(route('tutor.aktivasi'))->assertDontSee('aktivasi-tutor/', false);

        config(['edulink.mode_demo' => true, 'edulink.tautan_di_layar' => true]);
        $this->post(route('tutor.aktivasi.store'), ['kontak' => 'lestari@contoh.com'])->assertSessionHas('tautan_demo');

        // Each request still emailed the link (to the mail log in the demo).
        Mail::assertSentCount(3);

        $this->assertInstanceOf(AkunPengguna::class, new AkunPengguna);
    }

    /**
     * Reads config/edulink.php the way Laravel does at boot, with the given
     * EDULINK_MODE_DEMO and APP_ENV, then puts the process environment back.
     */
    private function modeDemoDari(?string $nilai, string $appEnv, string $variabel = 'EDULINK_MODE_DEMO', string $kunciConfig = 'mode_demo'): bool
    {
        $simpan = [];

        foreach ([$variabel => $nilai, 'APP_ENV' => $appEnv] as $kunci => $isi) {
            $simpan[$kunci] = [$_SERVER[$kunci] ?? null, $_ENV[$kunci] ?? null, getenv($kunci)];

            if ($isi === null) {
                unset($_SERVER[$kunci], $_ENV[$kunci]);
                putenv($kunci);
            } else {
                $_SERVER[$kunci] = $_ENV[$kunci] = $isi;
                putenv($kunci.'='.$isi);
            }
        }

        try {
            return (require config_path('edulink.php'))[$kunciConfig];
        } finally {
            foreach ($simpan as $kunci => [$server, $env, $proses]) {
                unset($_SERVER[$kunci], $_ENV[$kunci]);

                if ($server !== null) {
                    $_SERVER[$kunci] = $server;
                }

                if ($env !== null) {
                    $_ENV[$kunci] = $env;
                }

                putenv($proses === false ? $kunci : $kunci.'='.$proses);
            }
        }
    }

    /**
     * @return array<string, array{0: ?string, 1: string, 2: bool}>
     */
    public static function nilaiModeDemo(): array
    {
        return [
            'kosong di local' => ['', 'local', true],
            'kosong di testing' => ['', 'testing', true],
            'kosong di production' => ['', 'production', false],
            'tidak diisi di local' => [null, 'local', true],
            'tidak diisi di production' => [null, 'production', false],
            'true di production' => ['true', 'production', true],
            'false di local' => ['false', 'local', false],
            '1 di production' => ['1', 'production', true],
            '0 di local' => ['0', 'local', false],
            'on di production' => ['on', 'production', true],
            'off di local' => ['off', 'local', false],
            'ON huruf besar' => ['ON', 'production', true],
            'salah ketik jatuh ke default local' => ['ture', 'local', true],
            'salah ketik jatuh ke default production' => ['ture', 'production', false],
        ];
    }

    #[DataProvider('nilaiModeDemo')]
    public function test_demo_mode_reads_the_env_value_or_falls_back_to_the_default(?string $nilai, string $appEnv, bool $harapan): void
    {
        $this->assertSame($harapan, $this->modeDemoDari($nilai, $appEnv));
    }

    /**
     * @return array<string, array{0: ?string, 1: string, 2: bool}>
     */
    public static function nilaiTautanDiLayar(): array
    {
        return [
            'tidak diisi di local' => [null, 'local', false],
            'kosong di production' => ['', 'production', false],
            'tidak diisi di testing' => [null, 'testing', true],
            'true di local' => ['true', 'local', true],
            'on di production' => ['on', 'production', true],
            'false di testing' => ['false', 'testing', false],
            'salah ketik jatuh ke default' => ['ya', 'local', false],
        ];
    }

    #[DataProvider('nilaiTautanDiLayar')]
    public function test_links_on_screen_are_off_by_default_except_in_testing(?string $nilai, string $appEnv, bool $harapan): void
    {
        $this->assertSame($harapan, $this->modeDemoDari($nilai, $appEnv, 'EDULINK_TAUTAN_DI_LAYAR', 'tautan_di_layar'));
    }
}
