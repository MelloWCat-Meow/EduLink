<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Peran;
use App\Http\Controllers\Controller;
use App\Models\AkunPengguna;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function index(): View
    {
        $daftarAkun = AkunPengguna::query()
            ->where('peran', Peran::Ortu)
            ->withCount(['siswa', 'pesanan'])
            ->orderBy('id_akun')
            ->paginate(20);

        return view('admin.pengguna', ['daftarAkun' => $daftarAkun]);
    }
}
