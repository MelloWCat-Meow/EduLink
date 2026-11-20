<?php

namespace App\Http\Controllers\Tutor;

use App\Enums\LangkahPendaftaran;
use App\Enums\Peran;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\DaftarTutorRequest;
use App\Services\PendaftaranTutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DaftarTutorController extends Controller
{
    /**
     * Step 1 for a visitor. A signed-in tutor continues where they left off.
     */
    public function create(Request $request, PendaftaranTutor $pendaftaran): View|RedirectResponse
    {
        $akun = $request->user();

        if ($akun !== null) {
            return $akun->hasPeran(Peran::Tutor) && $akun->tutor !== null
                ? redirect()->to($pendaftaran->tujuan($akun->tutor))
                : redirect()->route($akun->peran->homeRoute());
        }

        return view('tutor.pendaftaran.langkah', [
            'langkah' => LangkahPendaftaran::DataDiri,
            'tutor' => null,
            'kekurangan' => [],
        ]);
    }

    /**
     * Creates the tutor account and draft, signs in, and opens step 2.
     */
    public function store(DaftarTutorRequest $request, PendaftaranTutor $pendaftaran): RedirectResponse
    {
        $tutor = $pendaftaran->daftar($request->safe()->only(['nama', 'kontak', 'no_hp', 'password']));

        Auth::loginUsingId($tutor->id_akun);
        $request->session()->regenerate();

        return redirect()->route('tutor.pendaftaran.langkah', LangkahPendaftaran::Profil)
            ->with('status', 'Akun tutor dibuat dan langkah 1 tersimpan. Anda bisa keluar kapan saja dan melanjutkan nanti.');
    }
}
