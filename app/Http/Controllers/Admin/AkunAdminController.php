<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AkunAdminController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.akun', ['akun' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['nama' => ['required', 'string', 'max:100']], ['nama.required' => 'Nama belum diisi.', 'nama.max' => 'Nama terlalu panjang, maksimal 100 karakter.']);

        $request->user()->update(['nama' => trim($data['nama'])]);

        return redirect()->route('admin.akun.edit')->with('status', 'Nama tersimpan.');
    }
}
