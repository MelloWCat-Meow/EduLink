<?php

namespace App\Policies;

use App\Models\AkunPengguna;
use App\Models\Siswa;

class SiswaPolicy
{
    public function update(AkunPengguna $akun, Siswa $siswa): bool
    {
        return $siswa->id_akun === $akun->id_akun;
    }

    public function delete(AkunPengguna $akun, Siswa $siswa): bool
    {
        return $siswa->id_akun === $akun->id_akun;
    }
}
