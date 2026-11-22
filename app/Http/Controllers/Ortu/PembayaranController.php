<?php

namespace App\Http\Controllers\Ortu;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\PengembalianDana;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    public function index(Request $request): View
    {
        $daftarPembayaran = Pembayaran::query()
            ->whereIn('id_pesanan', Pesanan::query()->milikAkun($request->user()->id_akun)->select('id_pesanan'))
            ->with(['pesanan.tutor', 'pesanan.mapel'])
            ->latest()
            ->orderByDesc('id_pembayaran')
            ->paginate(10);

        $daftarPengembalian = PengembalianDana::query()
            ->whereIn('id_pesanan', Pesanan::query()->milikAkun($request->user()->id_akun)->select('id_pesanan'))
            ->with(['pesanan.tutor', 'pesanan.mapel'])
            ->latest()
            ->get();

        return view('ortu.pembayaran.index', ['daftarPembayaran' => $daftarPembayaran, 'daftarPengembalian' => $daftarPengembalian]);
    }
}
