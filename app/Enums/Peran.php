<?php

namespace App\Enums;

/**
 * Account roles listed for Akun_Pengguna.peran in the EduLink report.
 * Ortu, Tutor and Admin can sign in; Siswa is reserved.
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
    public function label(): string
    {
        return match ($this) {
            self::Ortu => 'Orang tua',
            self::Siswa => 'Siswa',
            self::Tutor => 'Tutor',
            self::Admin => 'Admin',
        };
    }

    public function homeRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.beranda',
            // EnsureStatusTutor sends a tutor whose application is not verified yet on to the form or status page.
            self::Tutor => 'tutor.area',
            default => 'beranda',
        };
    }
}
