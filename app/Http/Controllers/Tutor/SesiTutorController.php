<?php

namespace App\Http\Controllers\Tutor;

use App\Enums\Kehadiran;
use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\CatatSesiRequest;
use App\Models\SesiPembelajaran;
use App\Services\AlurPesanan;
use App\Services\KonfirmasiSesi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SesiTutorController extends Controller
{
    public function index(Request $request): View
    {
        $idTutor = $request->user()->tutor->id_tutor;
        $relasi = ['pesanan.siswa', 'pesanan.mapel'];
        $berjalan = fn ($pesanan) => $pesanan->where('status', StatusPesanan::Dibayar);

        return view('tutor.dasbor.sesi', [
            'perluDicatat' => SesiPembelajaran::query()->milikTutor($idTutor)->whereHas('pesanan', $berjalan)
                ->whereNull('kehadiran')->where('waktu', '<=', now())->with($relasi)->orderBy('waktu')->get(),
            'mendatang' => SesiPembelajaran::query()->milikTutor($idTutor)->whereHas('pesanan', $berjalan)
                ->whereNull('kehadiran')->where('waktu', '>', now())->with($relasi)->orderBy('waktu')->limit(30)->get(),
            'tercatat' => SesiPembelajaran::query()->milikTutor($idTutor)->whereNotNull('kehadiran')
                ->with($relasi)->latest('waktu')->paginate(10)->withQueryString(),
            'pilihanKehadiran' => Kehadiran::cases(),
        ]);
    }

    public function catat(CatatSesiRequest $request, SesiPembelajaran $sesi, AlurPesanan $alurPesanan): RedirectResponse
    {
        $alurPesanan->catatKehadiran($sesi, Kehadiran::from($request->validated('kehadiran')), $request->validated('catatan'), oleh: $request->user());

        return back()->with('status', 'Sesi tercatat dan menunggu konfirmasi orang tua. Tanpa jawaban dalam '.KonfirmasiSesi::BATAS_JAM.' jam, sesi dikonfirmasi otomatis dan bagian Anda masuk saldo.');
    }
}
