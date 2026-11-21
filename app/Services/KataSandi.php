<?php

namespace App\Services;

use App\Models\AkunPengguna;
use App\Notifications\TautanResetKataSandi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * "Lupa kata sandi" through Laravel's password broker (tokens keyed by kontak,
 * 60 minutes, single use), and changing the password while signed in. Both
 * end every other session of the account: AuthenticateSession logs out any
 * session still holding the old password hash, and with the database driver
 * the other session rows are deleted as well.
 */
class KataSandi
{
    public function __construct(private Pemberitahu $pemberitahu) {}

    /**
     * Emails a reset link when the email belongs to an account. Returns the
     * link (for mode demo) or null; the caller answers the same either way.
     */
    public function kirimTautanReset(string $kontak): ?string
    {
        $url = null;
        $menit = (int) config('auth.passwords.akun_pengguna.expire', 60);

        $status = Password::broker()->sendResetLink(['kontak' => mb_strtolower(trim($kontak))], function (AkunPengguna $akun, string $token) use (&$url, $menit): string {
            $url = route('password.reset', ['token' => $token, 'kontak' => $akun->kontak]);
            $akun->notify(new TautanResetKataSandi($url, $menit));

            return Password::RESET_LINK_SENT;
        });

        return $status === Password::RESET_LINK_SENT ? $url : null;
    }

    /**
     * @param  array{kontak: string, token: string, password: string, password_confirmation: string}  $data
     * @return string the broker status (Password::PASSWORD_RESET on success)
     */
    public function reset(array $data): string
    {
        return Password::broker()->reset($data, function (AkunPengguna $akun, string $password): void {
            $this->simpan($akun, $password);
            $this->putusSesiLain($akun, null);
            $this->pemberitahu->kataSandiDiubah($akun);
        });
    }

    /**
     * Changes the password of the signed-in account and keeps only this session.
     */
    public function ganti(AkunPengguna $akun, string $password, Request $request): void
    {
        $this->simpan($akun, $password);

        $request->session()->regenerate();
        // AuthenticateSession compares this with the account's current hash.
        $request->session()->put('password_hash_'.auth()->getDefaultDriver(), $akun->getAuthPassword());

        $this->putusSesiLain($akun, $request->session()->getId());
        $this->pemberitahu->kataSandiDiubah($akun);
    }

    private function simpan(AkunPengguna $akun, string $password): void
    {
        $akun->forceFill(['kredensial' => $password]);
        $akun->setRememberToken(Str::random(60));
        $akun->save();
    }

    /**
     * Deletes the account's other stored sessions (database driver only).
     */
    public function putusSesiLain(AkunPengguna $akun, ?string $kecualiSesi): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $akun->id_akun)
            ->when($kecualiSesi, fn ($query) => $query->where('id', '!=', $kecualiSesi))
            ->delete();
    }
}
