<?php

namespace App\Http\Controllers;

use App\Enums\StatusKonfirmasiSesi;
use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use App\Models\SesiPembelajaran;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Parent home: what needs doing now, then the orders still in progress.
 */
class BerandaController extends Controller
{
    public function __invoke(Request $request): View
    {
        $idAkun = $request->user()->id_akun;
        $relasi = ['siswa', 'tutor', 'mapel'];

        $menungguBayar = Pesanan::query()->milikAkun($idAkun)
            ->where('status', StatusPesanan::MenungguPembayaran)
            ->with($relasi)->orderBy('ditahan_hingga')->get();

        $sesiTerdekat = SesiPembelajaran::query()
            ->whereHas('pesanan', fn ($pesanan) => $pesanan->milikAkun($idAkun)->where('status', StatusPesanan::Dibayar))
            ->whereNull('kehadiran')
            ->where('waktu', '>=', now()->subHours(2))
            ->with(['pesanan.siswa', 'pesanan.tutor', 'pesanan.mapel'])
            ->orderBy('waktu')
            ->first();

        $menungguKonfirmasi = SesiPembelajaran::query()
            ->whereHas('pesanan', fn ($pesanan) => $pesanan->milikAkun($idAkun))
            ->where('status_konfirmasi', StatusKonfirmasiSesi::MenungguKonfirmasi)
            ->with(['pesanan.siswa', 'pesanan.tutor', 'pesanan.mapel'])
            ->orderBy('dicatat_pada')
            ->get();

        $belumDiulas = Pesanan::query()->milikAkun($idAkun)
            ->where('status', StatusPesanan::Selesai)
            ->whereDoesntHave('ulasan')
            ->with($relasi)->latest('updated_at')->get();

        $aktif = Pesanan::query()->milikAkun($idAkun)
            ->whereIn('status', StatusPesanan::active())
            ->with($relasi)->latest()->get();

        return view('ortu.beranda', [
            'akun' => $request->user(),
            'menungguBayar' => $menungguBayar,
            'sesiTerdekat' => $sesiTerdekat,
            'menungguKonfirmasi' => $menungguKonfirmasi,
            'belumDiulas' => $belumDiulas,
            'aktif' => $aktif,
            'jumlahAnak' => $request->user()->siswa()->count(),
        ]);
    }
}
