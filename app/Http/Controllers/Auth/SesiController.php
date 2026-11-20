<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Peran;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\MasukRequest;
use App\Services\PendaftaranTutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SesiController extends Controller
{
    /**
     * Show the sign-in page.
     */
    public function create(): View
    {
        return view('auth.masuk');
    }

    /**
     * Sign in and send each role to its own home page. A tutor goes straight
     * to the page for their application status, never to a remembered URL.
     */
    public function store(MasukRequest $request, PendaftaranTutor $pendaftaran): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $akun = $request->user();

        if ($akun->hasPeran(Peran::Tutor) && $akun->tutor !== null) {
            $request->session()->forget('url.intended');

            return redirect()->to($pendaftaran->tujuan($akun->tutor));
        }

        return redirect()->intended(route($akun->peran->homeRoute()));
    }

    /**
     * Sign out and end the session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}
