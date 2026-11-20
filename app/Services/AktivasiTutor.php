<?php

namespace App\Services;

use App\Enums\Peran;
use App\Enums\StatusTutor;
use App\Exceptions\AksiDitolak;
use App\Mail\TautanAktivasiTutor;
use App\Models\AkunPengguna;
use App\Models\Tutor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Activation for internal tutors: their tutor record already exists, so
 * activation only creates the sign-in account and links it. The tutor row is
 * never copied.
 */
class AktivasiTutor
{
    public const MENIT_BERLAKU = 60;

    /**
     * Emails a signed link when the address belongs to an internal tutor
     * without an account. Returns the link (for the local demo) or null.
     * The caller must answer the same way in both cases.
     */
    public function kirimTautan(string $email): ?string
    {
        $tutor = Tutor::query()
            ->where('status', StatusTutor::Internal)
            ->whereNull('id_akun')
            ->whereRaw('LOWER(kontak) = ?', [mb_strtolower(trim($email))])
            ->first();

        if ($tutor === null) {
            return null;
        }

        $url = URL::temporarySignedRoute('tutor.aktivasi.sandi', now()->addMinutes(self::MENIT_BERLAKU), ['tutor' => $tutor->id_tutor]);

        Mail::to($tutor->kontak, $tutor->nama)->send(new TautanAktivasiTutor($tutor, $url));

        return $url;
    }

    /**
     * The link is usable while the tutor has no account, so it works only once.
     */
    public function bisaDiaktifkan(Tutor $tutor): bool
    {
        return $tutor->isInternal() && $tutor->id_akun === null;
    }

    public function aktifkan(Tutor $tutor, string $password): AkunPengguna
    {
        return DB::transaction(function () use ($tutor, $password): AkunPengguna {
            $tutor = Tutor::query()->lockForUpdate()->findOrFail($tutor->id_tutor);

            if (! $this->bisaDiaktifkan($tutor)) {
                throw new AksiDitolak('Tautan aktivasi ini sudah dipakai. Silakan masuk dengan email dan kata sandi Anda.');
            }

            $kontak = mb_strtolower($tutor->kontak);

            if (AkunPengguna::query()->where('kontak', $kontak)->exists()) {
                throw new AksiDitolak('Email ini sudah dipakai akun lain. Hubungi admin EduLink untuk menautkan akun Anda.');
            }

            $akun = new AkunPengguna(['nama' => $tutor->nama, 'kontak' => $kontak, 'kredensial' => $password]);
            $akun->peran = Peran::Tutor;
            $akun->save();

            $tutor->id_akun = $akun->id_akun;
            $tutor->save();

            return $akun;
        });
    }
}
