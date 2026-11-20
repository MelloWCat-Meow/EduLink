<?php

namespace App\Policies;

use App\Enums\Peran;
use App\Models\AkunPengguna;
use App\Models\Pesanan;
use App\Models\Siswa;

/**
 * Parents act only on orders for their own children. Whether the order's
 * status allows the action is checked by AlurPesanan and the form requests.
 */
class PesananPolicy
{
    public function view(AkunPengguna $akun, Pesanan $pesanan): bool
    {
        return $akun->peran === Peran::Ortu
            && Siswa::query()->whereKey($pesanan->id_siswa)->where('id_akun', $akun->id_akun)->exists();
    }

    public function update(AkunPengguna $akun, Pesanan $pesanan): bool
    {
        return $this->view($akun, $pesanan);
    }
}
