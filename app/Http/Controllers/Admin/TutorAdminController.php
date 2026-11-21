<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusTutor;
use App\Enums\StatusVerifikasiTutor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MapelTutorRequest;
use App\Models\MataPelajaran;
use App\Models\Tutor;
use App\Services\KelolaAkun;
use App\Services\KelolaTutor;
use App\Services\SaldoTutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TutorAdminController extends Controller
{
    public function index(Request $request): View
    {
        $status = StatusVerifikasiTutor::tryFrom((string) $request->query('status'));
        $jenis = StatusTutor::tryFrom((string) $request->query('jenis'));
        $aktif = $request->query('aktif');
        $cari = trim((string) $request->query('cari'));

        $daftarTutor = Tutor::query()
            ->when($status, fn ($query) => $query->where('status_verifikasi', $status))
            ->when($jenis, fn ($query) => $query->where('status', $jenis))
            ->when($aktif === 'nonaktif', fn ($query) => $query->whereHas('akun', fn ($akun) => $akun->where('aktif', false)))
            ->when($aktif === 'aktif', fn ($query) => $query->whereDoesntHave('akun', fn ($akun) => $akun->where('aktif', false)))
            ->when($cari !== '', fn ($query) => $query->where('nama', 'like', '%'.$cari.'%'))
            ->with(['akun', 'profil'])
            ->withCount('pesanan')
            ->denganRating()
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        return view('admin.tutor.index', [
            'daftarTutor' => $daftarTutor,
            'filter' => ['status' => $status?->value, 'jenis' => $jenis?->value, 'aktif' => $aktif, 'cari' => $cari],
        ]);
    }

    public function show(Tutor $tutor, SaldoTutor $saldoTutor, KelolaAkun $kelolaAkun): View
    {
        $tutor->load([
            'akun.riwayatStatus' => fn ($riwayat) => $riwayat->with('admin')->latest('dicatat_pada'),
            'profil', 'mataPelajaran',
            'riwayatVerifikasi' => fn ($riwayat) => $riwayat->with('akun')->latest('dicatat_pada')->latest('id'),
        ]);

        return view('admin.tutor.show', [
            'tutor' => $tutor,
            'rating' => Tutor::query()->denganRating()->withCount('pesanan')->findOrFail($tutor->id_tutor),
            'saldo' => $saldoTutor->satu($tutor->id_tutor),
            'mapelPerJenjang' => MataPelajaran::query()->orderBy('nama')->get()->groupBy('jenjang'),
            'pesananDibayar' => $tutor->akun ? $kelolaAkun->pesananDibayarBerjalan($tutor->akun) : collect(),
            'tampilDiPencarian' => $tutor->isListed(),
        ]);
    }

    public function mapel(MapelTutorRequest $request, Tutor $tutor, KelolaTutor $kelolaTutor): RedirectResponse
    {
        $kelolaTutor->ubahMapel($tutor, $request->validated('mapel'), $request->validated('alasan'), $request->user());

        return redirect()->route('admin.tutor.show', $tutor)->with('status', 'Mata pelajaran diubah, dicatat di riwayat, dan dikirim ke tutor.');
    }
}
