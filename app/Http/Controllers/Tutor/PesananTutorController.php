<?php

namespace App\Http\Controllers\Tutor;

use App\Enums\JenisKeluhan;
use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AlasanRequest;
use App\Models\Pesanan;
use App\Services\AlurPesanan;
use App\Services\JadwalUlang;
use App\Services\KontakWhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Orders placed with the signed-in tutor. Every status change goes through
 * AlurPesanan; the route's "kelolaTutor" policy keeps other tutors out.
 */
class PesananTutorController extends Controller
{
    public function __construct(private AlurPesanan $alurPesanan) {}

    public function index(Request $request): View
    {
        $status = StatusPesanan::tryFrom((string) $request->query('status'));

        $daftarPesanan = Pesanan::query()
            ->where('id_tutor', $request->user()->tutor->id_tutor)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->with(['siswa', 'mapel', 'jadwal'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('tutor.dasbor.pesanan.index', ['daftarPesanan' => $daftarPesanan, 'statusDipilih' => $status]);
    }

    public function show(Request $request, Pesanan $pesanan, KontakWhatsApp $kontakWhatsApp, JadwalUlang $jadwalUlang): View
    {
        $jadwalUlang->kedaluwarsakan();

        $pesanan->load([
            'siswa.wali', 'mapel', 'jadwal', 'komisi', 'ulasan',
            'sesi' => fn ($sesi) => $sesi->orderBy('waktu')->with('permintaanJadwalUlang'),
            'keluhan' => fn ($keluhan) => $keluhan->where('id_akun', $request->user()->id_akun)->latest(),
            'pengembalian' => fn ($pengembalian) => $pengembalian->withCount('sesi')->oldest(),
            'riwayat' => fn ($riwayat) => $riwayat->with('akun')->orderBy('dicatat_pada')->orderBy('id'),
        ]);

        return view('tutor.dasbor.pesanan.show', [
            'pesanan' => $pesanan,
            'pilihanKeluhan' => JenisKeluhan::pilihanFormulir(),
            'kontak' => $kontakWhatsApp->untuk($request->user(), $pesanan),
        ]);
    }

    public function terima(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $this->alurPesanan->terima($pesanan, $request->user());

        return redirect()->route('tutor.pesanan.show', $pesanan)
            ->with('status', 'Pesanan diterima. Orang tua punya waktu '.AlurPesanan::MASA_TAHAN_JAM.' jam untuk membayar.');
    }

    public function tolak(AlasanRequest $request, Pesanan $pesanan): RedirectResponse
    {
        $this->alurPesanan->tolakOlehTutor($pesanan, $request->validated('alasan'), $request->user());

        return redirect()->route('tutor.pesanan.show', $pesanan)->with('status', 'Pesanan ditolak dan slot kembali tersedia.');
    }

    public function batal(AlasanRequest $request, Pesanan $pesanan): RedirectResponse
    {
        $this->alurPesanan->batalkanOlehTutor($pesanan, $request->validated('alasan'), $request->user());

        return redirect()->route('tutor.pesanan.show', $pesanan)->with('status', 'Pesanan dibatalkan dan slot kembali tersedia.');
    }
}
