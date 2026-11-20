<?php

namespace App\Policies;

use App\Enums\Peran;
use App\Models\AkunPengguna;
use App\Models\JadwalKetersediaan;

class JadwalKetersediaanPolicy
{
    public function kelola(AkunPengguna $akun, JadwalKetersediaan $jadwal): bool
    {
        return $akun->peran === Peran::Tutor && $akun->tutor?->id_tutor === $jadwal->id_tutor;
    }
}
