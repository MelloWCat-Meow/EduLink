<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\AktivasiRequest;
use App\Http\Requests\Tutor\BuatSandiRequest;
use App\Models\Tutor;
use App\Services\AktivasiTutor;
use App\Services\PendaftaranTutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AktivasiController extends Controller
{
    /**
     * The same answer whether or not the email matched, so the page cannot be
     * used to find out who teaches at the bimbel.
     */
    public const PESAN_TERKIRIM = 'Kalau email itu terdaftar sebagai pengajar bimbel EduLink yang belum punya akun, tautan aktivasi sudah kami kirim. Periksa kotak masuk dan folder spam. Tautan berlaku 60 menit.';

    public function __construct(private AktivasiTutor $aktivasi) {}

    public function create(): View
    {
        return view('tutor.aktivasi.minta');
    }

    public function store(AktivasiRequest $request): RedirectResponse
    {
        $url = $this->aktivasi->kirimTautan($request->validated('kontak'));

        $redirect = redirect()->route('tutor.aktivasi')->with('status', self::PESAN_TERKIRIM);

        // Local demo only: there is no real inbox, so the link is shown on screen.
        if ($url !== null && app()->isLocal()) {
            $redirect->with('tautan_demo', $url);
        }

        return $redirect;
    }

    public function edit(Request $request, Tutor $tutor): View|Response
    {
        return $this->tolakJikaTidakBerlaku($request, $tutor)
            ?? view('tutor.aktivasi.sandi', ['tutor' => $tutor, 'aksi' => $request->fullUrl()]);
    }

    public function update(BuatSandiRequest $request, Tutor $tutor, PendaftaranTutor $pendaftaran): RedirectResponse|Response
    {
        if ($tolak = $this->tolakJikaTidakBerlaku($request, $tutor)) {
            return $tolak;
        }

        $akun = $this->aktivasi->aktifkan($tutor, $request->validated('password'));

        Auth::login($akun);
        $request->session()->regenerate();

        return redirect()->to($pendaftaran->tujuan($tutor->refresh()))
            ->with('status', 'Akun aktif. Data Anda dari bimbel sudah terisi; lengkapi tarif, kecamatan, dan jadwal, lalu kirim untuk diverifikasi.');
    }

    private function tolakJikaTidakBerlaku(Request $request, Tutor $tutor): ?Response
    {
        if (! $request->hasValidSignature()) {
            return response()->view('tutor.aktivasi.tidak-berlaku', [
                'judul' => 'Tautan tidak berlaku',
                'pesan' => 'Tautan aktivasi ini sudah kedaluwarsa atau tidak lengkap. Minta tautan baru dengan email Anda.',
            ], 403);
        }

        if (! $this->aktivasi->bisaDiaktifkan($tutor)) {
            return response()->view('tutor.aktivasi.tidak-berlaku', [
                'judul' => 'Tautan sudah dipakai',
                'pesan' => 'Akun untuk data tutor ini sudah aktif. Silakan masuk dengan email dan kata sandi yang Anda buat.',
            ], 410);
        }

        return null;
    }
}
