<?php

namespace Tests\Feature\Pesanan;

use App\Enums\MetodePembayaran;
use App\Enums\StatusPesanan;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Services\PemrosesPembayaran;
use App\Support\TandaTanganWebhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class WebhookPembayaranTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    private function kirimWebhook(Pembayaran $pembayaran, string $status, ?string $tandaTangan = null): TestResponse
    {
        return $this->postJson(route('webhooks.payment'), [
            'payment_id' => $pembayaran->id_pembayaran,
            'order_id' => $pembayaran->id_pesanan,
            'status' => $status,
            'signature' => $tandaTangan ?? TandaTanganWebhook::buat($pembayaran->id_pembayaran, $pembayaran->id_pesanan, $status),
        ]);
    }

    private function pembayaranMenunggu(?Pesanan $pesanan = null): Pembayaran
    {
        return app(PemrosesPembayaran::class)->mulai($pesanan ?? $this->pesananMenungguPembayaran(), MetodePembayaran::Qris);
    }

    public function test_successful_notification_marks_the_order_paid(): void
    {
        $pembayaran = $this->pembayaranMenunggu();

        $this->kirimWebhook($pembayaran, 'berhasil')->assertOk()->assertJson(['status' => 'berhasil']);

        $pesanan = $pembayaran->pesanan()->firstOrFail();
        $this->assertSame(StatusPesanan::Dibayar, $pesanan->status);
        $this->assertSame(1, $pesanan->komisi()->count());
    }

    public function test_the_same_notification_sent_twice_changes_nothing_the_second_time(): void
    {
        $pembayaran = $this->pembayaranMenunggu();
        $pesanan = $pembayaran->pesanan()->firstOrFail();

        $this->kirimWebhook($pembayaran, 'berhasil')->assertOk();
        $this->kirimWebhook($pembayaran, 'berhasil')->assertOk()->assertJson(['status' => 'berhasil']);

        $this->assertSame(1, $pesanan->pembayaran()->count());
        $this->assertSame(1, $pesanan->komisi()->count());
        $this->assertSame(4, $pesanan->sesi()->count());
        $this->assertSame(3, $pesanan->riwayat()->count());
    }

    public function test_a_late_failure_does_not_undo_a_successful_payment(): void
    {
        $pembayaran = $this->pembayaranMenunggu();

        $this->kirimWebhook($pembayaran, 'berhasil');
        $this->kirimWebhook($pembayaran, 'gagal')->assertOk()->assertJson(['status' => 'berhasil']);

        $this->assertSame(StatusPesanan::Dibayar, $pembayaran->pesanan()->firstOrFail()->status);
    }

    public function test_starting_payment_twice_reuses_the_pending_attempt(): void
    {
        $pesanan = $this->pesananMenungguPembayaran();
        $pertama = $this->pembayaranMenunggu($pesanan);

        $kedua = app(PemrosesPembayaran::class)->mulai($pesanan, MetodePembayaran::VirtualAccount);

        $this->assertSame($pertama->id_pembayaran, $kedua->id_pembayaran);
        $this->assertSame(1, $pesanan->pembayaran()->count());
    }

    public function test_parent_can_retry_after_a_failed_payment(): void
    {
        $pesanan = $this->pesananMenungguPembayaran();
        $gagal = $this->pembayaranMenunggu($pesanan);
        $this->kirimWebhook($gagal, 'gagal')->assertOk();

        $ulang = $this->pembayaranMenunggu($pesanan);
        $this->kirimWebhook($ulang, 'berhasil')->assertOk();

        $this->assertNotSame($gagal->id_pembayaran, $ulang->id_pembayaran);
        $this->assertSame(StatusPesanan::Dibayar, $pesanan->refresh()->status);
        $this->assertSame(['gagal', 'berhasil'], $pesanan->pembayaran()->orderBy('id_pembayaran')->pluck('status')->map->value->all());
    }

    public function test_payment_arriving_after_the_deadline_is_closed_as_failed_and_the_order_cancelled(): void
    {
        $pembayaran = $this->pembayaranMenunggu();
        $this->travel(25)->hours();

        $this->kirimWebhook($pembayaran, 'berhasil')->assertOk()->assertJson(['status' => 'gagal']);

        $this->assertSame(StatusPesanan::Batal, $pembayaran->pesanan()->firstOrFail()->status);
    }

    public function test_notification_with_a_wrong_signature_is_rejected(): void
    {
        $pembayaran = $this->pembayaranMenunggu();

        $this->kirimWebhook($pembayaran, 'berhasil', 'palsu')->assertUnprocessable()->assertJsonValidationErrors('signature');

        $this->assertSame(StatusPesanan::MenungguPembayaran, $pembayaran->pesanan()->firstOrFail()->status);
    }

    public function test_notification_for_a_payment_of_another_order_is_not_found(): void
    {
        $pembayaran = $this->pembayaranMenunggu();
        $lain = $this->pesananMenungguPembayaran();
        $status = 'berhasil';

        $this->postJson(route('webhooks.payment'), [
            'payment_id' => $pembayaran->id_pembayaran,
            'order_id' => $lain->id_pesanan,
            'status' => $status,
            'signature' => TandaTanganWebhook::buat($pembayaran->id_pembayaran, $lain->id_pesanan, $status),
        ])->assertNotFound();
    }
}
