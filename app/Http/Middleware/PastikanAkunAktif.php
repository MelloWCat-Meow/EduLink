<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs out an account the admin switched off, on its very next request.
 */
class PastikanAkunAktif
{
    public const PESAN = 'Akun Anda dinonaktifkan. Hubungi EduLink.';

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $akun = $request->user();

        if ($akun !== null && ! $akun->aktif) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['kontak' => self::PESAN]);
        }

        return $next($request);
    }
}
