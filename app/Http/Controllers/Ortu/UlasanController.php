<?php

namespace App\Http\Controllers\Ortu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ortu\UlasanRequest;
use App\Models\Pesanan;
use App\Services\Pemberitahu;
use Illuminate\Http\RedirectResponse;

class UlasanController extends Controller
{
    public function store(UlasanRequest $request, Pesanan $pesanan, Pemberitahu $pemberitahu): RedirectResponse
    {
        $pemberitahu->ulasanBaru($pesanan->ulasan()->create($request->validated()));

        return redirect()->route('pesanan.show', $pesanan)->with('status', 'Terima kasih, ulasan Anda sudah tersimpan.');
    }
}
