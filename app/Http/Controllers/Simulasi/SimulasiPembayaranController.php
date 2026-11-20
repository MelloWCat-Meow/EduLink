<?php

namespace App\Http\Controllers\Simulasi;

use App\Enums\StatusPembayaran;
use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Support\TandaTanganWebhook;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Stand-in for the payment gateway's checkout page. Local only.
 */
class SimulasiPembayaranController extends Controller
{
    public function __invoke(Pembayaran $pembayaran): View|RedirectResponse
    {
        if ($pembayaran->status !== StatusPembayaran::Pending) {
            return redirect()->route('pesanan.show', $pembayaran->id_pesanan)->with('status', 'Pembayaran '.$pembayaran->id_pembayaran.' sudah diproses.');
        }

        $pembayaran->load('pesanan.tutor');

        $tandaTangan = collect(['berhasil', 'gagal'])->mapWithKeys(fn (string $status) => [
            $status => TandaTanganWebhook::buat($pembayaran->id_pembayaran, $pembayaran->id_pesanan, $status),
        ]);

        return view('simulasi.pembayaran', ['pembayaran' => $pembayaran, 'tandaTangan' => $tandaTangan]);
    }
}
