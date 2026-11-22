<?php

namespace App\Notifications;

use App\Enums\JenisNotifikasi;
use App\Models\AkunPengguna;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * One in-app notification with a link to the page it is about. Important
 * kinds also go out by email; $email replaces the generic email when the
 * event already has its own (the verification decision).
 */
class Pemberitahuan extends Notification
{
    /**
     * @param  array<string, mixed>  $tambahan
     */
    public function __construct(
        public JenisNotifikasi $jenis,
        public string $judul,
        public string $pesan,
        public string $tautan,
        public ?Mailable $email = null,
        public array $tambahan = [],
    ) {}

    /**
     * @return list<string>
     */
    public function via(AkunPengguna $notifiable): array
    {
        return $this->jenis->pakaiEmail() ? ['database', 'mail'] : ['database'];
    }

    public function toMail(AkunPengguna $notifiable): MailMessage|Mailable
    {
        if ($this->email !== null) {
            return $this->email->to($notifiable->kontak, $notifiable->nama);
        }

        return (new MailMessage)
            ->subject($this->judul)
            ->greeting('Halo '.$notifiable->nama.',')
            ->line($this->pesan)
            ->action($this->jenis === JenisNotifikasi::SesiDicatat ? 'Konfirmasi atau sanggah' : 'Buka di EduLink', $this->tautan)
            ->salutation('Salam, EduLink Tutor');
    }

    /**
     * $tambahan carries ids the notification page needs for inline actions,
     * e.g. id_sesi for the Konfirmasi button of a logged session.
     *
     * @return array<string, mixed>
     */
    public function toArray(AkunPengguna $notifiable): array
    {
        return [
            'jenis' => $this->jenis->value,
            'judul' => $this->judul,
            'pesan' => $this->pesan,
            'tautan' => $this->tautan,
            ...$this->tambahan,
        ];
    }
}
