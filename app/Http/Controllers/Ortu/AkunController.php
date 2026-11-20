<?php

namespace App\Http\Controllers\Ortu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ortu\AkunRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The parent's own account: name and phone. The email is the sign-in name
 * and cannot be changed here.
 */
class AkunController extends Controller
{
    public function edit(Request $request): View
    {
        return view('ortu.akun.edit', ['akun' => $request->user()]);
    }

    public function update(AkunRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('akun.edit')->with('status', 'Data akun tersimpan.');
    }
}
