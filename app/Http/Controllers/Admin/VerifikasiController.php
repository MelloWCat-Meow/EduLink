<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusTutor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AlasanRequest;
use App\Models\Tutor;
use App\Services\VerifikasiTutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    public function index(): View
    {
        $antrean = Tutor::query()->menungguVerifikasi()
            ->with(['profil', 'mataPelajaran', 'dokumen'])
            ->withMin(['dokumen as diajukan_pada' => fn ($dokumen) => $dokumen->where('status_verifikasi', 'menunggu')], 'created_at')
            ->orderBy('diajukan_pada')
            ->get();

        return view('admin.verifikasi.index', ['antrean' => $antrean]);
    }

    public function show(Tutor $tutor): View
    {
        abort_unless($tutor->status === StatusTutor::Eksternal, 404);

        $tutor->load(['profil', 'mataPelajaran', 'dokumen' => fn ($dokumen) => $dokumen->orderBy('jenis'), 'jadwal']);

        return view('admin.verifikasi.show', ['tutor' => $tutor, 'tampilDiPencarian' => $tutor->isListed()]);
    }

    public function setujui(Tutor $tutor, VerifikasiTutor $verifikasi): RedirectResponse
    {
        $jumlah = $verifikasi->setujui($tutor);

        return redirect()->route('admin.verifikasi.index')
            ->with($jumlah > 0 ? 'status' : 'galat', $jumlah > 0
                ? "Berkas {$tutor->nama} disetujui. Profilnya sekarang muncul di pencarian."
                : "Tidak ada berkas {$tutor->nama} yang menunggu pemeriksaan.");
    }

    public function tolak(AlasanRequest $request, Tutor $tutor, VerifikasiTutor $verifikasi): RedirectResponse
    {
        $jumlah = $verifikasi->tolak($tutor, $request->string('alasan')->toString());

        return redirect()->route('admin.verifikasi.index')
            ->with($jumlah > 0 ? 'status' : 'galat', $jumlah > 0
                ? "Berkas {$tutor->nama} ditolak. Profilnya tidak muncul di pencarian."
                : "Tidak ada berkas {$tutor->nama} yang menunggu pemeriksaan.");
    }
}
