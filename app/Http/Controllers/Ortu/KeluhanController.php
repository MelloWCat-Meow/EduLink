<?php

namespace App\Http\Controllers\Ortu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ortu\KeluhanRequest;
use App\Models\Keluhan;
use App\Models\Pesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KeluhanController extends Controller
{
    public function index(Request $request): View
    {
        $daftarKeluhan = $request->user()->keluhan()
            ->with(['pesanan.tutor', 'pesanan.mapel'])
            ->latest()
            ->paginate(10);

        return view('ortu.keluhan.index', ['daftarKeluhan' => $daftarKeluhan]);
    }

    public function store(KeluhanRequest $request, Pesanan $pesanan): RedirectResponse
    {
        $keluhan = new Keluhan([...$request->validated(), 'id_pesanan' => $pesanan->id_pesanan]);
        $keluhan->id_akun = $request->user()->id_akun;
        $keluhan->save();

        return redirect()->route('pesanan.show', $pesanan)
            ->with('status', 'Keluhan '.$keluhan->id_keluhan.' terkirim. Tanggapan admin akan muncul di halaman Keluhan.');
    }
}
