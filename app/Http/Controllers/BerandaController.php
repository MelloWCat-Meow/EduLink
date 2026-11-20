<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class BerandaController extends Controller
{
    /**
     * The guardian's home page, listing the students under the account.
     */
    public function __invoke(Request $request): View
    {
        $akun = $request->user();

        return view('beranda', [
            'akun' => $akun,
            'daftarSiswa' => $akun->siswa()->orderBy('id_siswa')->get(),
        ]);
    }
}
