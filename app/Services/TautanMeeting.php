<?php

namespace App\Services;

use App\Enums\ModeBelajar;
use App\Enums\StatusPesanan;
use App\Exceptions\AksiDitolak;
use App\Models\Pesanan;
use App\Models\SesiPembelajaran;

/**
 * Meeting links the tutor shares for an online order: one default for the
 * order, optionally replaced for a single session. EduLink only stores and
 * shows the link; the meeting runs on the tutor's own Meet, Zoom, etc.
 * Ownership is checked by the route policy; the order rules live here.
 */
class TautanMeeting
{
    public function simpanUntukPesanan(Pesanan $pesanan, ?string $tautan): void
    {
        $this->pastikanBisaDiisi($pesanan);

        $pesanan->forceFill(['tautan_meeting' => $tautan])->save();
    }

    /**
     * An empty link removes the override, so the order's default applies again.
     */
    public function simpanUntukSesi(SesiPembelajaran $sesi, ?string $tautan): void
    {
        $this->pastikanBisaDiisi($sesi->pesanan()->firstOrFail());

        if ($sesi->kehadiran !== null) {
            throw new AksiDitolak('Sesi ini sudah dicatat, jadi tautannya tidak perlu diubah lagi.');
        }

        $sesi->update(['tautan_meeting' => $tautan]);
    }

    private function pastikanBisaDiisi(Pesanan $pesanan): void
    {
        if ($pesanan->mode !== ModeBelajar::Online) {
            throw new AksiDitolak('Tautan meeting hanya untuk pesanan online.');
        }

        if ($pesanan->status !== StatusPesanan::Dibayar) {
            throw new AksiDitolak('Tautan meeting bisa dibagikan setelah pesanan dibayar.');
        }
    }
}
