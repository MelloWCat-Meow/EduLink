<?php

namespace App\Services;

use App\Enums\StatusPencairan;
use App\Exceptions\AksiDitolak;
use App\Models\PencairanDanaTutor;
use App\Models\Tutor;
use App\Support\Format;
use Illuminate\Support\Facades\DB;

/**
 * Payout requests: menunggu -> disetujui -> dibayar. Approval needs enough
 * balance (successful payments - commission - earlier payouts).
 */
class PencairanDana
{
    public function __construct(private SaldoTutor $saldoTutor, private Pemberitahu $pemberitahu) {}

    /**
     * A tutor's payout request. Needs a payout account, at most one request
     * waiting at a time, and an amount within the available balance.
     */
    public function ajukan(Tutor $tutor, string $periode, string $nominal): PencairanDanaTutor
    {
        return DB::transaction(function () use ($tutor, $periode, $nominal): PencairanDanaTutor {
            $tutor = Tutor::query()->lockForUpdate()->findOrFail($tutor->id_tutor);

            if (! $tutor->punyaRekening()) {
                throw new AksiDitolak('Isi rekening pencairan dulu sebelum mengajukan pencairan.');
            }

            if ($tutor->pencairan()->where('status', StatusPencairan::Menunggu)->exists()) {
                throw new AksiDitolak('Masih ada pengajuan yang menunggu persetujuan admin. Ajukan lagi setelah pengajuan itu diproses.');
            }

            $saldo = $this->saldoTutor->satu($tutor->id_tutor)['saldo'];

            if (bccomp($nominal, $saldo, 2) === 1) {
                throw new AksiDitolak('Nominal melebihi saldo tersedia ('.Format::rupiah($saldo).').');
            }

            $pencairan = $tutor->pencairan()->make(['periode' => $periode, 'nominal' => $nominal]);
            $pencairan->status = StatusPencairan::Menunggu;
            $pencairan->save();

            $this->pemberitahu->pengajuanPencairanBaru($pencairan);

            return $pencairan;
        });
    }

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
            $this->pemberitahu->pencairanDiperbarui($terkunci);
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
            $this->pemberitahu->pencairanDiperbarui($terkunci);
        });
    }
}
