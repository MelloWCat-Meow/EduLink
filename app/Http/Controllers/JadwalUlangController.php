<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\AlasanRequest;
use App\Http\Requests\AjukanJadwalUlangRequest;
use App\Models\PermintaanJadwalUlang;
use App\Models\SesiPembelajaran;
use App\Services\JadwalUlang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Rescheduling for both sides of an order. Every rule is in JadwalUlang;
 * it also refuses anyone who is not a party of the order.
 */
class JadwalUlangController extends Controller
{
    public function __construct(private JadwalUlang $jadwalUlang) {}

    public function ajukan(AjukanJadwalUlangRequest $request, SesiPembelajaran $sesi): RedirectResponse
    {
        $this->jadwalUlang->ajukan($sesi, $request->user(), $request->waktuBaru(), $request->validated('alasan'));

        return back()->with('status', 'Pengajuan jadwal ulang terkirim. Sesi pindah setelah pihak lain menyetujuinya.');
    }

    public function setujui(Request $request, PermintaanJadwalUlang $permintaan): RedirectResponse
    {
        $this->jadwalUlang->setujui($permintaan, $request->user());

        return back()->with('status', 'Jadwal ulang disetujui. Waktu sesi sudah diperbarui.');
    }

    public function tolak(AlasanRequest $request, PermintaanJadwalUlang $permintaan): RedirectResponse
    {
        $this->jadwalUlang->tolak($permintaan, $request->user(), $request->validated('alasan'));

        return back()->with('status', 'Jadwal ulang ditolak. Sesi tetap pada waktu semula.');
    }
}
