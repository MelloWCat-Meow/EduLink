<?php

namespace App\Http\Controllers\Tutor;

use App\Enums\LangkahPendaftaran;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\DataDiriRequest;
use App\Http\Requests\Tutor\JadwalRequest;
use App\Http\Requests\Tutor\ProfilTutorRequest;
use App\Models\MataPelajaran;
use App\Models\Tutor;
use App\Services\PendaftaranTutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * The numbered application form for a signed-in tutor whose application is
 * still draf or perlu_perbaikan. Every step saves on its own.
 */
class PendaftaranController extends Controller
{
    public function __construct(private PendaftaranTutor $pendaftaran) {}

    public function show(Request $request, LangkahPendaftaran $langkah): View
    {
        $tutor = $this->tutor($request);

        return view('tutor.pendaftaran.langkah', [
            'langkah' => $langkah,
            'tutor' => $tutor,
            'kekurangan' => $this->pendaftaran->kekurangan($tutor),
            'mapelPerJenjang' => MataPelajaran::query()->orderBy('nama')->get()->groupBy('jenjang')->sortBy(fn ($daftar, string $jenjang): int => array_search($jenjang, ProfilTutorRequest::JENJANG, true)),
            'infoBerkas' => $tutor->dokumen->mapWithKeys(fn ($dokumen): array => [$dokumen->id_dokumen => [
                'ukuran' => Storage::disk('local')->exists($dokumen->berkas) ? Storage::disk('local')->size($dokumen->berkas) : null,
                'ekstensi' => strtoupper(pathinfo($dokumen->berkas, PATHINFO_EXTENSION)),
            ]]),
        ]);
    }

    public function dataDiri(DataDiriRequest $request): RedirectResponse
    {
        $this->pendaftaran->simpanDataDiri($this->tutor($request), $request->validated());

        return $this->lanjut(LangkahPendaftaran::DataDiri);
    }

    public function profil(ProfilTutorRequest $request): RedirectResponse
    {
        $this->pendaftaran->simpanProfil($this->tutor($request), $request->safe()->except(['jenjang', 'foto']), $request->file('foto'));

        return $this->lanjut(LangkahPendaftaran::Profil);
    }

    public function jadwal(JadwalRequest $request): RedirectResponse
    {
        $this->pendaftaran->simpanJadwal($this->tutor($request), $request->slot());

        return $this->lanjut(LangkahPendaftaran::Jadwal);
    }

    public function kirim(Request $request): RedirectResponse
    {
        $tutor = $this->tutor($request);

        if ($this->pendaftaran->kekurangan($tutor) !== []) {
            return redirect()->route('tutor.pendaftaran.langkah', LangkahPendaftaran::Kirim)
                ->with('galat', 'Pendaftaran belum lengkap. Lengkapi bagian yang ditandai di bawah, lalu kirim lagi.');
        }

        $this->pendaftaran->kirim($tutor);

        return redirect()->route('tutor.status')
            ->with('status', 'Pendaftaran terkirim. Tim akademik akan memeriksanya dan mengabari Anda lewat email.');
    }

    private function tutor(Request $request): Tutor
    {
        return $request->user()->tutor->load(['profil', 'mataPelajaran', 'dokumen' => fn ($dokumen) => $dokumen->orderBy('jenis')->orderBy('created_at'), 'jadwal' => fn ($jadwal) => $jadwal->orderBy('jam')]);
    }

    private function lanjut(LangkahPendaftaran $tersimpan): RedirectResponse
    {
        $berikutnya = LangkahPendaftaran::cases()[$tersimpan->nomor()];

        return redirect()->route('tutor.pendaftaran.langkah', $berikutnya)
            ->with('status', "Langkah {$tersimpan->nomor()} ({$tersimpan->label()}) tersimpan.");
    }
}
