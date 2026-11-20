<?php

namespace App\Enums;

/**
 * Account roles listed for Akun_Pengguna.peran in the EduLink report.
 * Only Ortu and Admin can sign in for now; Siswa and Tutor are reserved.
 */
enum Peran: string
{
    case Ortu = 'ortu';
    case Siswa = 'siswa';
    case Tutor = 'tutor';
    case Admin = 'admin';

    /**
     * Name of the route each role lands on after signing in.
     */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.beranda',
            default => 'beranda',
        };
    }
}
