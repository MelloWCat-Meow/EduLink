<?php

namespace App\Services;

use App\Enums\Peran;
use App\Enums\StatusPesanan;
use App\Models\AkunPengguna;
use App\Models\Pesanan;
use App\Models\Tutor;
use App\Support\NomorWhatsApp;

/**
 * The WhatsApp link between a parent and the tutor of one order. The other
 * side's number is only ever turned into a link here, and only for a party
 * of a paid order (or one finished in the last 14 days), so bookings and
 * payments stay on the platform.
 */
class KontakWhatsApp
{
    public const HARI_SETELAH_SELESAI = 14;

    public function masihBolehDihubungi(Pesanan $pesanan): bool
    {
        if ($pesanan->status === StatusPesanan::Dibayar) {
            return true;
        }

        if ($pesanan->status !== StatusPesanan::Selesai) {
            return false;
        }

        $selesaiPada = $pesanan->riwayat()->where('status', StatusPesanan::Selesai)->max('dicatat_pada');

        return $selesaiPada !== null && now()->subDays(self::HARI_SETELAH_SELESAI)->lessThanOrEqualTo($selesaiPada);
    }

    public function pihakPesanan(AkunPengguna $akun, Pesanan $pesanan): bool
    {
        return match ($akun->peran) {
            Peran::Ortu => $pesanan->siswa()->where('id_akun', $akun->id_akun)->exists(),
            Peran::Tutor => Tutor::query()->whereKey($pesanan->id_tutor)->where('id_akun', $akun->id_akun)->exists(),
            default => false,
        };
    }

    /**
     * What the order page shows for this account: a ready link, or a plain
     * explanation. Never the number itself.
     *
     * @return array{tautan: string|null, keterangan: string|null, label: string}
     */
    public function untuk(AkunPengguna $akun, Pesanan $pesanan): array
    {
        $dariTutor = $akun->hasPeran(Peran::Tutor);
        $label = $dariTutor ? 'Chat orang tua via WhatsApp' : 'Chat tutor via WhatsApp';

        if (! $this->pihakPesanan($akun, $pesanan)) {
            return ['tautan' => null, 'keterangan' => null, 'label' => $label];
        }

        if (! $this->masihBolehDihubungi($pesanan)) {
            $keterangan = $pesanan->status === StatusPesanan::Selesai
                ? 'Tombol WhatsApp hanya tersedia sampai '.self::HARI_SETELAH_SELESAI.' hari setelah pesanan selesai.'
                : 'Tombol WhatsApp muncul setelah pesanan dibayar. Sebelum itu, semua lewat EduLink.';

            return ['tautan' => null, 'keterangan' => $keterangan, 'label' => $label];
        }

        $pesanan->loadMissing(['siswa.wali', 'tutor', 'mapel']);
        $ortu = $pesanan->siswa->wali;
        $tutor = $pesanan->tutor;

        $tautan = $dariTutor
            ? NomorWhatsApp::tautan($ortu->no_hp, "Halo Bapak/Ibu {$ortu->nama}, saya {$tutor->nama}, tutor {$pesanan->mapel->label()} untuk {$pesanan->siswa->nama} dari EduLink, terkait pesanan {$pesanan->id_pesanan}.")
            : NomorWhatsApp::tautan($tutor->no_hp, "Halo Kak {$tutor->nama}, saya {$ortu->nama}, wali dari {$pesanan->siswa->nama}, untuk pesanan {$pesanan->id_pesanan} ({$pesanan->mapel->label()}).");

        $keterangan = $tautan === null
            ? ($dariTutor ? 'Orang tua belum mengisi nomor HP. Hubungi admin EduLink kalau perlu berkoordinasi.' : 'Tutor belum mengisi nomor HP. Hubungi admin EduLink kalau perlu berkoordinasi.')
            : null;

        return ['tautan' => $tautan, 'keterangan' => $keterangan, 'label' => $label];
    }
}
