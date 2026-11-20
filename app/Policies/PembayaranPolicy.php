<?php

namespace App\Policies;

use App\Models\AkunPengguna;
use App\Models\Pembayaran;
use App\Models\Pesanan;

class PembayaranPolicy
{
    public function view(AkunPengguna $akun, Pembayaran $pembayaran): bool
    {
        return Pesanan::query()->whereKey($pembayaran->id_pesanan)->milikAkun($akun->id_akun)->exists();
    }
}
