<?php

namespace App\Services;

use App\Enums\StatusPencairan;
use App\Exceptions\AksiDitolak;
use App\Models\PencairanDanaTutor;
use Illuminate\Support\Facades\DB;

/**
 * Payout requests: menunggu -> disetujui -> dibayar. Approval needs enough
 * balance (successful payments - commission - earlier payouts).
 */
class PencairanDana
{
    public function __construct(private SaldoTutor $saldoTutor) {}

    public function setujui(PencairanDanaTutor $pencairan): void
    {
        DB::transaction(function () use ($pencairan): void {
            $terkunci = PencairanDanaTutor::query()->whereKey($pencairan->getKey())->lockForUpdate()->firstOrFail();

            if ($terkunci->status !== StatusPencairan::Menunggu) {
                throw new AksiDitolak('Pengajuan ini sudah tidak menunggu persetujuan.');
            }

            $saldo = $this->saldoTutor->satu($terkunci->id_tutor)['saldo'];

            if (bccomp((string) $terkunci->nominal, $saldo, 2) === 1) {
                throw new AksiDitolak('Saldo tutor tidak cukup untuk pencairan ini.');
            }

            $terkunci->status = StatusPencairan::Disetujui;
            $terkunci->save();
            $pencairan->setRawAttributes($terkunci->getAttributes(), true);
        });
    }

    public function tandaiDibayar(PencairanDanaTutor $pencairan): void
    {
        DB::transaction(function () use ($pencairan): void {
            $terkunci = PencairanDanaTutor::query()->whereKey($pencairan->getKey())->lockForUpdate()->firstOrFail();

            if ($terkunci->status !== StatusPencairan::Disetujui) {
                throw new AksiDitolak('Hanya pengajuan yang sudah disetujui yang bisa ditandai dibayar.');
            }

            $terkunci->status = StatusPencairan::Dibayar;
            $terkunci->save();
            $pencairan->setRawAttributes($terkunci->getAttributes(), true);
        });
    }
}
