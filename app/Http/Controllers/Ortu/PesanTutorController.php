<?php

namespace App\Http\Controllers\Ortu;

use App\Enums\StatusJadwal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ortu\PesanTutorRequest;
use App\Models\Tutor;
use App\Services\AlurPesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Booking in two steps: fill the form, check the total, then confirm.
 */
class PesanTutorController extends Controller
{
    public function create(Request $request, string $tutor): View
    {
        $tutor = $this->tutorTampil($tutor);

        return view('ortu.pesan.create', [
            'tutor' => $tutor,
            'daftarAnak' => $request->user()->siswa()->orderBy('id_siswa')->get(),
            'jadwalTersedia' => $tutor->jadwal->where('status', StatusJadwal::Tersedia)->sortBy(fn ($jadwal) => $jadwal->hari->isoWeekday())->values(),
        ]);
    }

    public function store(PesanTutorRequest $request, Tutor $tutor, AlurPesanan $alurPesanan): View|RedirectResponse
    {
        abort_unless($tutor->isListed(), 404);

        $data = $request->dataPesanan();

        if (! $request->boolean('konfirmasi')) {
            return view('ortu.pesan.konfirmasi', [
                'tutor' => $tutor,
                'data' => $data,
                'jadwal' => $tutor->jadwal()->findOrFail($data['id_jadwal']),
                'total' => AlurPesanan::hitungTotal((string) $tutor->profil->tarif, $data['durasi'], $data['jumlah_pertemuan']),
            ]);
        }

        $pesanan = $alurPesanan->buat($data, $request->user());

        return redirect()->route('pesanan.show', $pesanan)
            ->with('status', 'Pesanan '.$pesanan->id_pesanan.' terkirim. Tutor punya waktu '.AlurPesanan::MASA_TAHAN_JAM.' jam untuk menerima.');
    }

    private function tutorTampil(string $idTutor): Tutor
    {
        return Tutor::query()->tampil()
            ->with(['profil', 'mataPelajaran' => fn ($mapel) => $mapel->orderBy('jenjang')->orderBy('nama'), 'jadwal' => fn ($jadwal) => $jadwal->orderBy('jam')])
            ->findOrFail($idTutor);
    }
}
