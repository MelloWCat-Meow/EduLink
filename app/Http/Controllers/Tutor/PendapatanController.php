<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\PencairanRequest;
use App\Http\Requests\Tutor\RekeningRequest;
use App\Services\LayananTutor;
use App\Services\PencairanDana;
use App\Services\SaldoTutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendapatanController extends Controller
{
    public function index(Request $request, SaldoTutor $saldoTutor): View
    {
        $tutor = $request->user()->tutor;

        return view('tutor.dasbor.pendapatan', [
            'tutor' => $tutor,
            'saldo' => $saldoTutor->satu($tutor->id_tutor),
            'rincian' => $saldoTutor->rincian($tutor->id_tutor),
            'riwayatPencairan' => $tutor->pencairan()->latest()->get(),
        ]);
    }

    public function rekening(RekeningRequest $request, LayananTutor $layanan): RedirectResponse
    {
        $layanan->simpanRekening($request->user()->tutor, $request->validated());

        return redirect()->route('tutor.pendapatan.index')->with('status', 'Rekening pencairan tersimpan.');
    }

    public function ajukan(PencairanRequest $request, PencairanDana $pencairanDana): RedirectResponse
    {
        $pencairan = $pencairanDana->ajukan($request->user()->tutor, $request->validated('periode'), (string) $request->validated('nominal'));

        return redirect()->route('tutor.pendapatan.index')
            ->with('status', "Pengajuan {$pencairan->id_pencairan} terkirim dan menunggu persetujuan admin.");
    }
}
