<?php

namespace App\Http\Controllers\Simulasi;

use App\Enums\StatusPembayaran;
use App\Http\Controllers\Controller;
use App\Http\Requests\Simulasi\WebhookPembayaranRequest;
use App\Services\PemrosesPembayaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Imitates the payment gateway's webhook. Local only: the route is not
 * registered in production.
 */
class WebhookPembayaranController extends Controller
{
    public function __invoke(WebhookPembayaranRequest $request, PemrosesPembayaran $pemroses): JsonResponse|RedirectResponse
    {
        $pembayaran = $pemroses->terimaNotifikasi(
            $request->string('payment_id')->toString(),
            $request->string('order_id')->toString(),
            StatusPembayaran::from($request->string('status')->toString()),
        );

        if ($request->expectsJson()) {
            return response()->json([
                'payment_id' => $pembayaran->id_pembayaran,
                'order_id' => $pembayaran->id_pesanan,
                'status' => $pembayaran->status->value,
            ]);
        }

        // The simulator page posts here from the browser; send the parent back to the order.
        return redirect()
            ->route('pesanan.show', $pembayaran->id_pesanan)
            ->with('status', $pembayaran->status === StatusPembayaran::Berhasil
                ? 'Pembayaran berhasil. Jadwal sesi sudah dibuat.'
                : 'Pembayaran gagal. Anda bisa mencoba lagi sebelum batas waktu.');
    }
}
