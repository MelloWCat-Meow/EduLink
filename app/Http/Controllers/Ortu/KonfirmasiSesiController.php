<?php

namespace App\Http\Controllers\Ortu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ortu\SanggahSesiRequest;
use App\Models\SesiPembelajaran;
use App\Services\KonfirmasiSesi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The parent confirms or disputes a session the tutor logged. KonfirmasiSesi
 * refuses anyone who is not the parent of that order.
 */
class KonfirmasiSesiController extends Controller
{
    public function __construct(private KonfirmasiSesi $konfirmasiSesi) {}

    public function konfirmasi(Request $request, SesiPembelajaran $sesi): RedirectResponse
    {
        $this->konfirmasiSesi->konfirmasi($sesi, $request->user());

        return redirect()->to(route('pesanan.show', $sesi->id_pesanan).'#sesi-'.$sesi->id_sesi)
            ->with('status', 'Terima kasih, sesi dikonfirmasi.');
    }

    public function sanggah(SanggahSesiRequest $request, SesiPembelajaran $sesi): RedirectResponse
    {
        $keluhan = $this->konfirmasiSesi->sanggah($sesi, $request->user(), $request->validated('alasan'));

        return redirect()->to(route('pesanan.show', $sesi->id_pesanan).'#sesi-'.$sesi->id_sesi)
            ->with('status', "Sanggahan tercatat sebagai keluhan {$keluhan->id_keluhan}. Admin akan memutuskan dan mengabari Anda.");
    }
}
