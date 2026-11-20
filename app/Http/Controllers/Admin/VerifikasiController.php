<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AlasanRequest;
use App\Http\Requests\Admin\PerbaikanRequest;
use App\Models\Tutor;
use App\Services\PendaftaranTutor;
use App\Services\VerifikasiTutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    public function __construct(private VerifikasiTutor $verifikasi) {}

    /**
     * Applications waiting for a decision, the one sent earliest first.
     */
    public function index(): View
    {
        $antrean = Tutor::query()->menungguVerifikasi()
            ->with(['profil', 'mataPelajaran'])
            ->withCount('dokumen')
            ->orderBy('diajukan_pada')
            ->get();

        return view('admin.verifikasi.index', ['antrean' => $antrean]);
    }

    public function show(Tutor $tutor, PendaftaranTutor $pendaftaran): View
    {
        $tutor->load([
            'profil',
            'mataPelajaran',
            'dokumen' => fn ($dokumen) => $dokumen->orderBy('jenis')->orderBy('created_at'),
            'jadwal' => fn ($jadwal) => $jadwal->orderBy('jam'),
            'riwayatVerifikasi' => fn ($riwayat) => $riwayat->with('akun')->latest('dicatat_pada')->latest('id'),
        ]);

        return view('admin.verifikasi.show', [
            'tutor' => $tutor,
            'tampilDiPencarian' => $tutor->isListed(),
            'kekurangan' => $pendaftaran->kekurangan($tutor),
            'ukuranBerkas' => $tutor->dokumen->mapWithKeys(fn ($dokumen): array => [
                $dokumen->id_dokumen => Storage::disk('local')->exists($dokumen->berkas) ? Storage::disk('local')->size($dokumen->berkas) : null,
            ]),
        ]);
    }

    public function setujui(Request $request, Tutor $tutor): RedirectResponse
    {
        $this->verifikasi->setujui($tutor, $request->user());

        return redirect()->route('admin.verifikasi.index')
            ->with('status', "Pendaftaran {$tutor->nama} disetujui. Profilnya sekarang muncul di pencarian dan data dikirim ke EduManage.");
    }

    public function perbaikan(PerbaikanRequest $request, Tutor $tutor): RedirectResponse
    {
        $this->verifikasi->mintaPerbaikan($tutor, $request->user(), $request->validated('catatan'), $request->bagian(), $request->dokumen());

        return redirect()->route('admin.verifikasi.index')
            ->with('status', "Permintaan perbaikan dikirim ke {$tutor->nama}. Pendaftarannya kembali ke antrean setelah dikirim ulang.");
    }

    public function tolak(AlasanRequest $request, Tutor $tutor): RedirectResponse
    {
        $this->verifikasi->tolak($tutor, $request->user(), $request->validated('alasan'));

        return redirect()->route('admin.verifikasi.index')
            ->with('status', "Pendaftaran {$tutor->nama} ditolak. Tutor tidak bisa mengajukan ulang.");
    }
}
