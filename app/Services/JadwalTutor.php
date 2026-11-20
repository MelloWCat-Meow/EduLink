<?php

namespace App\Services;

use App\Enums\Hari;
use App\Enums\StatusJadwal;
use App\Exceptions\AksiDitolak;
use App\Models\JadwalKetersediaan;
use App\Models\Pesanan;
use App\Models\Tutor;
use Illuminate\Support\Facades\DB;

/**
 * A verified tutor's weekly slots. Only a free (tersedia) slot may change.
 * A slot that past orders point at is never rewritten or deleted: it is
 * switched off (nonaktif) and a new slot is made, so order history keeps
 * the day and time that was booked.
 */
class JadwalTutor
{
    public function tambah(Tutor $tutor, Hari $hari, string $jam): JadwalKetersediaan
    {
        return DB::transaction(function () use ($tutor, $hari, $jam): JadwalKetersediaan {
            $sama = $this->cari($tutor, $hari, $jam);

            if ($sama !== null && $sama->status !== StatusJadwal::Nonaktif) {
                throw new AksiDitolak("Slot {$sama->label()} sudah ada. Setiap hari dan jam hanya boleh satu slot.");
            }

            if ($sama !== null) {
                $sama->update(['status' => StatusJadwal::Tersedia]);

                return $sama;
            }

            return $tutor->jadwal()->create(['hari' => $hari, 'jam' => $jam.':00', 'status' => StatusJadwal::Tersedia]);
        });
    }

    public function ubah(JadwalKetersediaan $jadwal, Hari $hari, string $jam): JadwalKetersediaan
    {
        return DB::transaction(function () use ($jadwal, $hari, $jam): JadwalKetersediaan {
            $jadwal = $this->kunciYangBolehDiubah($jadwal, 'diubah');

            if ($jadwal->hari === $hari && substr($jadwal->jam, 0, 5) === $jam) {
                return $jadwal;
            }

            $tutor = $jadwal->tutor()->firstOrFail();

            if (! $this->punyaRiwayat($jadwal)) {
                $sama = $this->cari($tutor, $hari, $jam);

                if ($sama !== null && $sama->status !== StatusJadwal::Nonaktif) {
                    throw new AksiDitolak("Slot {$sama->label()} sudah ada. Setiap hari dan jam hanya boleh satu slot.");
                }

                $sama?->delete();
                $jadwal->update(['hari' => $hari, 'jam' => $jam.':00']);

                return $jadwal;
            }

            $baru = $this->tambah($tutor, $hari, $jam);
            $jadwal->update(['status' => StatusJadwal::Nonaktif]);

            return $baru;
        });
    }

    public function hapus(JadwalKetersediaan $jadwal): void
    {
        DB::transaction(function () use ($jadwal): void {
            $jadwal = $this->kunciYangBolehDiubah($jadwal, 'dihapus');

            $this->punyaRiwayat($jadwal)
                ? $jadwal->update(['status' => StatusJadwal::Nonaktif])
                : $jadwal->delete();
        });
    }

    private function kunciYangBolehDiubah(JadwalKetersediaan $jadwal, string $aksi): JadwalKetersediaan
    {
        $jadwal = JadwalKetersediaan::query()->lockForUpdate()->findOrFail($jadwal->id_jadwal);

        $alasan = match ($jadwal->status) {
            StatusJadwal::Ditahan => 'sedang ditahan oleh pesanan yang menunggu jawaban atau pembayaran',
            StatusJadwal::Terisi => 'sudah terisi oleh pesanan yang sedang berjalan',
            StatusJadwal::Nonaktif => 'sudah dihapus',
            StatusJadwal::Tersedia => null,
        };

        if ($alasan !== null) {
            throw new AksiDitolak("Slot {$jadwal->label()} tidak bisa {$aksi} karena {$alasan}. Slot bisa {$aksi} lagi setelah pesanan itu selesai atau batal.");
        }

        return $jadwal;
    }

    private function cari(Tutor $tutor, Hari $hari, string $jam): ?JadwalKetersediaan
    {
        return $tutor->jadwal()->where('hari', $hari)->where('jam', $jam.':00')->lockForUpdate()->first();
    }

    private function punyaRiwayat(JadwalKetersediaan $jadwal): bool
    {
        return Pesanan::query()->where('id_jadwal', $jadwal->id_jadwal)->exists();
    }
}
