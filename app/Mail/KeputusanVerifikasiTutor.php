<?php

namespace App\Mail;

use App\Enums\LangkahPendaftaran;
use App\Enums\StatusVerifikasiTutor;
use App\Models\Tutor;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * The admin's decision on an application, sent to the tutor.
 */
class KeputusanVerifikasiTutor extends Mailable
{
    /**
     * @param  array<string, string|null>  $bagian
     */
    public function __construct(public Tutor $tutor, public StatusVerifikasiTutor $keputusan, public ?string $catatan = null, public array $bagian = []) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: match ($this->keputusan) {
            StatusVerifikasiTutor::Terverifikasi => 'Pendaftaran tutor EduLink disetujui',
            StatusVerifikasiTutor::PerluPerbaikan => 'Pendaftaran tutor EduLink perlu diperbaiki',
            default => 'Hasil pemeriksaan pendaftaran tutor EduLink',
        });
    }

    public function content(): Content
    {
        return new Content(view: 'mail.tutor.keputusan', with: [
            'labelBagian' => collect($this->bagian)->mapWithKeys(fn (?string $catatan, string $kunci): array => [LangkahPendaftaran::from($kunci)->label() => $catatan])->all(),
        ]);
    }
}
