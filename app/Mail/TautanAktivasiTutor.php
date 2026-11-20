<?php

namespace App\Mail;

use App\Models\Tutor;
use App\Services\AktivasiTutor;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TautanAktivasiTutor extends Mailable
{
    public function __construct(public Tutor $tutor, public string $url) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Aktifkan akun tutor EduLink Anda');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.tutor.aktivasi', with: ['menit' => AktivasiTutor::MENIT_BERLAKU]);
    }
}
