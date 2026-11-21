<?php

namespace App\Http\Controllers\Ortu;

use App\Enums\MetodePembayaran;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ortu\BayarRequest;
use App\Models\Pesanan;
use App\Services\PemrosesPembayaran;
use Illuminate\Http\RedirectResponse;

class BayarController extends Controller
{
    /**
     * Opens a payment attempt and hands the parent to the payment gateway
     * (the local simulator until a real gateway is connected).
     */
    public function __invoke(BayarRequest $request, Pesanan $pesanan, PemrosesPembayaran $pemroses): RedirectResponse
    {
        abort_unless(config('edulink.mode_demo'), 503, 'Payment gateway belum tersambung.');

        $pembayaran = $pemroses->mulai($pesanan, MetodePembayaran::from($request->input('metode')));

        return redirect()->route('simulasi.pembayaran', $pembayaran);
    }
}
