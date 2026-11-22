<?php

namespace App\Policies;

use App\Enums\Peran;
use App\Models\AkunPengguna;
use App\Models\Pesanan;
use App\Models\SesiPembelajaran;

class SesiPembelajaranPolicy
{
    /**
     * Only the tutor of the order logs its sessions.
     */
    public function catat(AkunPengguna $akun, SesiPembelajaran $sesi): bool
    {
        return $akun->peran === Peran::Tutor
            && Pesanan::query()->whereKey($sesi->id_pesanan)->where('id_tutor', $akun->tutor?->id_tutor)->exists();
    }
}
