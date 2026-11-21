<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AlasanRequest;
use App\Models\Pesanan;
use App\Models\Tutor;
use App\Services\AlurPesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Order monitoring. Read-only, except cancelling a problem order with a recorded reason.
 */
class PesananController extends Controller
{
    public function index(Request $request): View
    {
        $status = StatusPesanan::tryFrom((string) $request->query('status'));
        $idTutor = $request->filled('tutor') ? (string) $request->query('tutor') : null;
        $periode = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->query('periode')) ? (string) $request->query('periode') : null;

        $daftarPesanan = Pesanan::query()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($idTutor, fn ($query) => $query->where('id_tutor', $idTutor))
            ->when($periode, function ($query) use ($periode): void {
                $mulai = Carbon::createFromFormat('!Y-m', $periode);
                $query->whereBetween('created_at', [$mulai, $mulai->copy()->endOfMonth()]);
            })
            ->with(['siswa.wali', 'tutor', 'mapel'])
            ->latest()
            ->orderByDesc('id_pesanan')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pesanan.index', [
            'daftarPesanan' => $daftarPesanan,
            'statusDipilih' => $status,
            'tutorDipilih' => $idTutor,
            'periode' => $periode,
            'pilihanTutor' => Tutor::query()->orderBy('nama')->get(['id_tutor', 'nama']),
        ]);
    }

    public function show(Pesanan $pesanan): View
    {
        $pesanan->load([
            'siswa.wali', 'tutor', 'mapel', 'jadwal', 'komisi', 'ulasan',
            'sesi' => fn ($sesi) => $sesi->orderBy('waktu'),
            'pembayaran' => fn ($pembayaran) => $pembayaran->orderBy('created_at'),
            'keluhan' => fn ($keluhan) => $keluhan->latest(),
            'pengembalianDana',
            'riwayat' => fn ($riwayat) => $riwayat->with('akun')->orderBy('dicatat_pada')->orderBy('id'),
        ]);

        return view('admin.pesanan.show', ['pesanan' => $pesanan]);
    }

    public function batal(AlasanRequest $request, Pesanan $pesanan, AlurPesanan $alurPesanan): RedirectResponse
    {
        $alurPesanan->batalkanOlehAdmin($pesanan, $request->string('alasan')->toString(), $request->user());

        return redirect()->route('admin.pesanan.show', $pesanan)->with('status', "Pesanan {$pesanan->id_pesanan} dibatalkan. Alasannya tercatat di riwayat.");
    }
}
