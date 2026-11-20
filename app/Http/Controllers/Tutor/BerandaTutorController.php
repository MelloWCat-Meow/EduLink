<?php

namespace App\Http\Controllers\Tutor;

use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\SesiPembelajaran;
use App\Models\Tutor;
use App\Services\SaldoTutor;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Home of the tutor dashboard: what needs an answer or a log entry now.
 */
class BerandaTutorController extends Controller
{
    public function __invoke(Request $request, SaldoTutor $saldoTutor): View
    {
        $tutor = $request->user()->tutor;
        $idTutor = $tutor->id_tutor;
        $relasiSesi = ['pesanan.siswa', 'pesanan.mapel'];

        $belumDicatat = SesiPembelajaran::query()->milikTutor($idTutor)
            ->whereHas('pesanan', fn ($pesanan) => $pesanan->where('status', StatusPesanan::Dibayar))
            ->whereNull('kehadiran')->where('waktu', '<=', now())
            ->with($relasiSesi)->orderBy('waktu')->get();

        $sesiDekat = SesiPembelajaran::query()->milikTutor($idTutor)
            ->whereHas('pesanan', fn ($pesanan) => $pesanan->where('status', StatusPesanan::Dibayar))
            ->whereNull('kehadiran')->whereBetween('waktu', [now(), now()->addDay()->endOfDay()])
            ->with($relasiSesi)->orderBy('waktu')->get();

        return view('tutor.dasbor.beranda', [
            'tutor' => $tutor,
            'permintaan' => Pesanan::query()->where('id_tutor', $idTutor)->where('status', StatusPesanan::MenungguKonfirmasiTutor)
                ->with(['siswa', 'mapel', 'jadwal'])->orderBy('ditahan_hingga')->get(),
            'sesiDekat' => $sesiDekat,
            'belumDicatat' => $belumDicatat,
            'menungguBayar' => Pesanan::query()->where('id_tutor', $idTutor)->where('status', StatusPesanan::MenungguPembayaran)->count(),
            'saldo' => $saldoTutor->satu($idTutor),
            'rating' => Tutor::query()->denganRating()->findOrFail($idTutor),
            'tampilDiPencarian' => $tutor->isListed(),
        ]);
    }
}
