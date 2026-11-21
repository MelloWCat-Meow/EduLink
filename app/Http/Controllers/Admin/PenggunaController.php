<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Peran;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AlasanRequest;
use App\Models\AkunPengguna;
use App\Services\KelolaAkun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Parent and tutor accounts. Admins can switch them off and on with a
 * reason; admin accounts are listed nowhere here and cannot be switched off.
 */
class PenggunaController extends Controller
{
    public function __construct(private KelolaAkun $kelolaAkun) {}

    public function index(Request $request): View
    {
        $peran = in_array($request->query('peran'), ['ortu', 'tutor'], true) ? Peran::from($request->query('peran')) : null;

        $daftarAkun = AkunPengguna::query()
            ->whereIn('peran', $peran ? [$peran] : [Peran::Ortu, Peran::Tutor])
            ->withCount(['siswa', 'pesanan'])
            ->with('tutor')
            ->orderBy('id_akun')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pengguna', ['daftarAkun' => $daftarAkun, 'peranDipilih' => $peran]);
    }

    public function show(AkunPengguna $akun): View
    {
        abort_if($akun->hasPeran(Peran::Admin), 404);

        $akun->load(['siswa', 'tutor', 'riwayatStatus' => fn ($riwayat) => $riwayat->with('admin')->latest('dicatat_pada')->latest('id')]);

        return view('admin.pengguna-detail', [
            'akun' => $akun,
            'jumlahPesanan' => $akun->hasPeran(Peran::Tutor) ? ($akun->tutor?->pesanan()->count() ?? 0) : $akun->pesanan()->count(),
            'pesananDibayar' => $this->kelolaAkun->pesananDibayarBerjalan($akun),
        ]);
    }

    public function nonaktifkan(AlasanRequest $request, AkunPengguna $akun): RedirectResponse
    {
        $dibatalkan = $this->kelolaAkun->nonaktifkan($akun, $request->user(), $request->validated('alasan'));

        return redirect()->route('admin.pengguna.show', $akun)
            ->with('status', "Akun {$akun->nama} dinonaktifkan dan sesinya diputus. {$dibatalkan} pesanan tertunda dibatalkan otomatis.");
    }

    public function aktifkan(AlasanRequest $request, AkunPengguna $akun): RedirectResponse
    {
        $this->kelolaAkun->aktifkan($akun, $request->user(), $request->validated('alasan'));

        return redirect()->route('admin.pengguna.show', $akun)->with('status', "Akun {$akun->nama} aktif kembali.");
    }
}
