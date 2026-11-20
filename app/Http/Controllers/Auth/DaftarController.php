<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Peran;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\DaftarRequest;
use App\Models\AkunPengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DaftarController extends Controller
{
    /**
     * Show the guardian (orang tua) sign-up page.
     */
    public function create(): View
    {
        return view('auth.daftar');
    }

    /**
     * Create a guardian account and sign it in. This page only ever creates
     * the ortu role; admin accounts come from the seeder.
     */
    public function store(DaftarRequest $request): RedirectResponse
    {
        $akun = DB::transaction(function () use ($request): AkunPengguna {
            $akun = new AkunPengguna([
                'nama' => $request->validated('nama'),
                'kontak' => $request->validated('kontak'),
                'kredensial' => $request->validated('password'),
            ]);
            $akun->peran = Peran::Ortu;
            $akun->save();

            return $akun;
        });

        Auth::login($akun);

        $request->session()->regenerate();

        return redirect()->intended(route(Peran::Ortu->homeRoute()));
    }
}
