<?php

namespace App\Policies;

use App\Enums\Peran;
use App\Models\AkunPengguna;
use App\Models\DokumenVerifikasi;

/**
 * Verification documents hold ID numbers and diplomas: only the tutor who
 * uploaded them and admins may open them.
 */
class DokumenVerifikasiPolicy
{
    public function view(AkunPengguna $akun, DokumenVerifikasi $dokumen): bool
    {
        return $akun->hasPeran(Peran::Admin) || $this->milikTutor($akun, $dokumen);
    }

    public function delete(AkunPengguna $akun, DokumenVerifikasi $dokumen): bool
    {
        return $this->milikTutor($akun, $dokumen);
    }

    private function milikTutor(AkunPengguna $akun, DokumenVerifikasi $dokumen): bool
    {
        return $akun->hasPeran(Peran::Tutor) && $akun->tutor?->id_tutor === $dokumen->id_tutor;
    }
}
