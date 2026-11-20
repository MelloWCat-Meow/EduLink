<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\MasukRequest;
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
     * Sign in and send each role to its own home page.
     */
    public function store(MasukRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->route($request->user()->peran->homeRoute());
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
