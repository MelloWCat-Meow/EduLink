<?php

namespace App\Services;

use App\Enums\StatusVerifikasi;
use App\Enums\StatusVerifikasiTutor;
use App\Models\Tutor;
use Illuminate\Support\Facades\DB;

/**
 * The admin's decision on a tutor's application. Approving makes every
 * pending document valid and the tutor terverifikasi, so the profile appears
 * in search; rejecting records the reason and keeps the tutor hidden.
 */
class VerifikasiTutor
{
    public function setujui(Tutor $tutor): int
    {
        return DB::transaction(function () use ($tutor): int {
            $jumlah = $tutor->dokumen()
                ->where('status_verifikasi', StatusVerifikasi::Menunggu)
                ->update(['status_verifikasi' => StatusVerifikasi::Valid, 'alasan_penolakan' => null, 'diperiksa_pada' => now(), 'updated_at' => now()]);

            $tutor->forceFill(['status_verifikasi' => StatusVerifikasiTutor::Terverifikasi, 'catatan_verifikasi' => null, 'bagian_perbaikan' => null])->save();

            return $jumlah;
        });
    }

    public function tolak(Tutor $tutor, string $alasan): int
    {
        return DB::transaction(function () use ($tutor, $alasan): int {
            $jumlah = $tutor->dokumen()
                ->where('status_verifikasi', StatusVerifikasi::Menunggu)
                ->update(['status_verifikasi' => StatusVerifikasi::Ditolak, 'alasan_penolakan' => $alasan, 'diperiksa_pada' => now(), 'updated_at' => now()]);

            $tutor->forceFill(['status_verifikasi' => StatusVerifikasiTutor::Ditolak, 'catatan_verifikasi' => $alasan])->save();

            return $jumlah;
        });
    }
}
