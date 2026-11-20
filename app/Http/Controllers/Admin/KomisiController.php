<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KomisiRequest;
use App\Models\KomisiBiayaLayanan;
use App\Models\Pengaturan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * The commission rate. A change applies to orders paid afterwards; commissions
 * already fixed on paid orders keep their own percentage and amount.
 */
class KomisiController extends Controller
{
    public function edit(): View
    {
        return view('admin.komisi', [
            'persenSekarang' => Pengaturan::komisiPersen(),
            'diubahPada' => Pengaturan::query()->find(Pengaturan::KOMISI_PERSEN)?->updated_at,
            'komisiTerakhir' => KomisiBiayaLayanan::query()->with('pesanan.tutor')->latest()->orderByDesc('id_komisi')->limit(10)->get(),
        ]);
    }

    public function update(KomisiRequest $request): RedirectResponse
    {
        $persen = rtrim(rtrim(number_format((float) $request->input('persentase'), 2, '.', ''), '0'), '.');
        Pengaturan::simpan(Pengaturan::KOMISI_PERSEN, $persen);

        return redirect()->route('admin.komisi.edit')->with('status', "Komisi diubah menjadi {$persen}%. Berlaku untuk pesanan yang dibayar mulai sekarang.");
    }
}
