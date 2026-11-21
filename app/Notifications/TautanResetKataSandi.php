<?php

namespace App\Notifications;

use App\Models\AkunPengguna;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email only: the reset link is a secret, so it never goes into the in-app list.
 */
class TautanResetKataSandi extends Notification
{
    public function __construct(public string $url, public int $menit) {}

    /**
     * @return list<string>
     */
    public function via(AkunPengguna $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(AkunPengguna $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Atur ulang kata sandi EduLink')
            ->greeting('Halo '.$notifiable->nama.',')
            ->line('Ada permintaan untuk mengatur ulang kata sandi akun EduLink Anda.')
            ->action('Buat kata sandi baru', $this->url)
            ->line("Tautan berlaku {$this->menit} menit dan hanya bisa dipakai sekali. Kalau Anda tidak memintanya, abaikan email ini; kata sandi Anda tidak berubah.")
            ->salutation('Salam, EduLink Tutor');
    }
}
