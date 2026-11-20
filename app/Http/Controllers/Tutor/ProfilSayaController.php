<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\ProfilAktifRequest;
use App\Services\LayananTutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfilSayaController extends Controller
{
    public function edit(Request $request): View
    {
        $tutor = $request->user()->tutor->load(['profil', 'mataPelajaran']);

        return view('tutor.dasbor.profil', ['tutor' => $tutor]);
    }

    public function update(ProfilAktifRequest $request, LayananTutor $layanan): RedirectResponse
    {
        $layanan->perbaruiProfil($request->user()->tutor, $request->safe()->except('foto'), $request->file('foto'));

        return redirect()->route('tutor.profil.edit')
            ->with('status', 'Profil tersimpan. Tarif baru berlaku untuk pesanan berikutnya; pesanan yang sudah ada tetap memakai tarif lamanya.');
    }
}
