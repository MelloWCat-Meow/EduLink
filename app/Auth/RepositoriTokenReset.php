<?php

namespace App\Auth;

use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Carbon;

/**
 * Laravel's database token repository with the key column renamed from
 * "email" to "kontak", to match akun_pengguna.
 */
class RepositoriTokenReset extends DatabaseTokenRepository
{
    protected function deleteExisting(CanResetPasswordContract $user)
    {
        return $this->getTable()->where('kontak', $user->getEmailForPasswordReset())->delete();
    }

    protected function getPayload($email, #[\SensitiveParameter] $token)
    {
        return ['kontak' => $email, 'token' => $this->hasher->make($token), 'created_at' => new Carbon];
    }

    public function exists(CanResetPasswordContract $user, #[\SensitiveParameter] $token)
    {
        $record = (array) $this->getTable()->where('kontak', $user->getEmailForPasswordReset())->first();

        return $record
            && ! $this->tokenExpired($record['created_at'])
            && $this->hasher->check($token, $record['token']);
    }

    public function recentlyCreatedToken(CanResetPasswordContract $user)
    {
        $record = (array) $this->getTable()->where('kontak', $user->getEmailForPasswordReset())->first();

        return $record && $this->tokenRecentlyCreated($record['created_at']);
    }

    public function delete(CanResetPasswordContract $user)
    {
        $this->deleteExisting($user);
    }
}
