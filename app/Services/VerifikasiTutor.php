<?php

namespace App\Services;

use App\Enums\StatusVerifikasi;
use App\Models\Tutor;
use Illuminate\Support\Facades\DB;

/**
 * The admin's decision on an external tutor's pending documents. Approving
 * makes every pending document valid, so the tutor appears in search;
 * rejecting marks them rejected with the reason, so the tutor stays hidden.
 */
class VerifikasiTutor
{
    public function setujui(Tutor $tutor): int
    {
        return DB::transaction(fn (): int => $tutor->dokumen()
            ->where('status_verifikasi', StatusVerifikasi::Menunggu)
            ->update(['status_verifikasi' => StatusVerifikasi::Valid, 'alasan_penolakan' => null, 'diperiksa_pada' => now(), 'updated_at' => now()]));
    }

    public function tolak(Tutor $tutor, string $alasan): int
    {
        return DB::transaction(fn (): int => $tutor->dokumen()
            ->where('status_verifikasi', StatusVerifikasi::Menunggu)
            ->update(['status_verifikasi' => StatusVerifikasi::Ditolak, 'alasan_penolakan' => $alasan, 'diperiksa_pada' => now(), 'updated_at' => now()]));
    }
}
