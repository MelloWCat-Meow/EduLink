<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ortu\KeluhanRequest;
use App\Models\Keluhan;
use App\Models\Pesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Complaints filed by the tutor about one of their orders, e.g. a paid order
 * the tutor can no longer teach (tutors cannot cancel a paid order themselves).
 */
class KeluhanTutorController extends Controller
{
    public function index(Request $request): View
    {
        $daftarKeluhan = $request->user()->keluhan()->with(['pesanan.siswa', 'pesanan.mapel'])->latest()->paginate(10);

        return view('tutor.dasbor.keluhan', ['daftarKeluhan' => $daftarKeluhan]);
    }

    public function store(KeluhanRequest $request, Pesanan $pesanan): RedirectResponse
    {
        $keluhan = new Keluhan([...$request->validated(), 'id_pesanan' => $pesanan->id_pesanan]);
        $keluhan->id_akun = $request->user()->id_akun;
        $keluhan->save();

        return redirect()->route('tutor.pesanan.show', $pesanan)
            ->with('status', 'Keluhan '.$keluhan->id_keluhan.' terkirim. Tanggapan admin akan muncul di halaman Keluhan.');
    }
}
