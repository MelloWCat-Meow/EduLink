<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GantiKataSandiRequest;
use App\Http\Requests\Auth\LupaKataSandiRequest;
use App\Http\Requests\Auth\ResetKataSandiRequest;
use App\Services\KataSandi;
use App\Support\TautanDiLayar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class KataSandiController extends Controller
{
    /**
     * The same answer whether or not the email has an account.
     */
    public const PESAN_TERKIRIM = 'Kalau email itu terdaftar di EduLink, tautan untuk membuat kata sandi baru sudah kami kirim. Periksa kotak masuk dan folder spam. Tautan berlaku 60 menit dan hanya bisa dipakai sekali.';

    public function __construct(private KataSandi $kataSandi) {}

    public function lupa(): View
    {
        return view('auth.lupa-kata-sandi');
    }

    public function kirim(LupaKataSandiRequest $request): RedirectResponse
    {
        $url = $this->kataSandi->kirimTautanReset($request->validated('kontak'));

        $redirect = redirect()->route('password.request')->with('status', self::PESAN_TERKIRIM);

        // Demo only, and only with EDULINK_TAUTAN_DI_LAYAR on: there is no real
        // inbox, so the link is shown on screen. Never for an admin account.
        if ($url !== null && TautanDiLayar::aktif()) {
            $redirect->with('tautan_demo', $url);
        }

        return $redirect;
    }

    public function buat(Request $request, string $token): View
    {
        return view('auth.reset-kata-sandi', ['token' => $token, 'kontak' => (string) $request->query('kontak')]);
    }

    public function simpan(ResetKataSandiRequest $request): RedirectResponse
    {
        $status = $this->kataSandi->reset($request->safe()->only(['kontak', 'token', 'password', 'password_confirmation']));

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('kontak'))
                ->withErrors(['kontak' => 'Tautan ini tidak berlaku: sudah dipakai, sudah lewat 60 menit, atau emailnya tidak cocok. Minta tautan baru.']);
        }

        return redirect()->route('login')->with('status', 'Kata sandi baru tersimpan dan semua perangkat lain sudah dikeluarkan. Silakan masuk dengan kata sandi baru.');
    }

    /**
     * Changing the password while signed in, for every role.
     */
    public function ganti(GantiKataSandiRequest $request): RedirectResponse
    {
        $this->kataSandi->ganti($request->user(), $request->validated('password'), $request);

        return back()->with('status', 'Kata sandi diubah. Perangkat lain yang masih masuk dengan akun ini sudah dikeluarkan.');
    }
}
