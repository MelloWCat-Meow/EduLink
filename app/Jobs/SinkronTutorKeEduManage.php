<?php

namespace App\Jobs;

use App\Models\Tutor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Sends an approved tutor to EduManage, the company's internal system.
 * EduManage has no API yet, so the payload is only written to the
 * "edumanage" log channel; replace handle() once the API exists.
 */
class SinkronTutorKeEduManage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public string $idTutor) {}

    public function handle(): void
    {
        $tutor = Tutor::query()->with('mataPelajaran')->find($this->idTutor);

        if ($tutor === null) {
            return;
        }

        Log::channel('edumanage')->info('Sinkron tutor ke EduManage', $this->payload($tutor));
    }

    /**
     * @return array{id_tutor: string, nama: string, status: string, status_verifikasi: string, kontak: string, no_hp: string|null, mapel: list<string>}
     */
    public function payload(Tutor $tutor): array
    {
        return [
            'id_tutor' => $tutor->id_tutor,
            'nama' => $tutor->nama,
            'status' => $tutor->status->value,
            'status_verifikasi' => $tutor->status_verifikasi->value,
            'kontak' => $tutor->kontak,
            'no_hp' => $tutor->no_hp,
            'mapel' => $tutor->mataPelajaran->pluck('kode_mapel')->values()->all(),
        ];
    }
}
