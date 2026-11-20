<?php

namespace App\Http\Controllers\Ortu;

use App\Enums\JenisKeluhan;
use App\Enums\MetodePembayaran;
use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Services\AlurPesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PesananController extends Controller
{
    public function index(Request $request): View
    {
        $status = StatusPesanan::tryFrom((string) $request->query('status'));

        $daftarPesanan = Pesanan::query()
            ->milikAkun($request->user()->id_akun)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->with(['siswa', 'tutor', 'mapel'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('ortu.pesanan.index', ['daftarPesanan' => $daftarPesanan, 'statusDipilih' => $status]);
    }

    public function show(Pesanan $pesanan): View
    {
        $pesanan->load([
            'siswa', 'tutor', 'mapel', 'jadwal', 'komisi', 'ulasan',
            'sesi' => fn ($sesi) => $sesi->orderBy('waktu'),
            'pembayaran' => fn ($pembayaran) => $pembayaran->orderBy('created_at'),
            'keluhan' => fn ($keluhan) => $keluhan->latest(),
            'riwayat' => fn ($riwayat) => $riwayat->orderBy('dicatat_pada')->orderBy('id'),
        ]);

        return view('ortu.pesanan.show', [
            'pesanan' => $pesanan,
            'pilihanMetode' => MetodePembayaran::cases(),
            'pilihanKeluhan' => JenisKeluhan::cases(),
        ]);
    }

    public function batal(Request $request, Pesanan $pesanan, AlurPesanan $alurPesanan): RedirectResponse
    {
        $alurPesanan->batalkanOlehOrtu($pesanan, $request->user());

        return redirect()->route('pesanan.show', $pesanan)->with('status', 'Pesanan dibatalkan dan jadwal tutor dilepas.');
    }
}
