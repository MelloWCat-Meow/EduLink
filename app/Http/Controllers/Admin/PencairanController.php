<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPencairan;
use App\Http\Controllers\Controller;
use App\Models\PencairanDanaTutor;
use App\Services\PencairanDana;
use App\Services\SaldoTutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PencairanController extends Controller
{
    public function index(SaldoTutor $saldoTutor): View
    {
        $daftarPencairan = PencairanDanaTutor::query()
            ->with('tutor')
            ->orderByRaw("CASE status WHEN 'menunggu' THEN 0 WHEN 'disetujui' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(15);

        $saldo = $saldoTutor->untuk($daftarPencairan->pluck('id_tutor')->unique()->values()->all());

        return view('admin.pencairan', ['daftarPencairan' => $daftarPencairan, 'saldo' => $saldo]);
    }

    public function setujui(PencairanDanaTutor $pencairan, PencairanDana $pencairanDana): RedirectResponse
    {
        $pencairanDana->setujui($pencairan);

        return back()->with('status', "Pencairan {$pencairan->id_pencairan} disetujui. Tandai dibayar setelah transfer ke tutor dilakukan.");
    }

    public function bayar(PencairanDanaTutor $pencairan, PencairanDana $pencairanDana): RedirectResponse
    {
        $pencairanDana->tandaiDibayar($pencairan);

        return back()->with('status', "Pencairan {$pencairan->id_pencairan} ditandai ".StatusPencairan::Dibayar->label().'.');
    }
}
