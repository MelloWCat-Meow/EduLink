<?php

namespace App\Console\Commands;

use App\Enums\Kehadiran;
use App\Exceptions\TransisiStatusTidakSah;
use App\Models\SesiPembelajaran;
use App\Services\AlurPesanan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Stands in for the tutor logging attendance after a session. Local only.
 */
#[Signature('edulink:simulasi-sesi {id_sesi : Nomor sesi, mis. SES0001} {kehadiran : hadir atau tidak_hadir} {--catatan= : Catatan tutor tentang sesi}')]
#[Description('Simulasi tutor mencatat kehadiran sesi (khusus lokal)')]
class SimulasiSesi extends Command
{
    public function isEnabled(): bool
    {
        return ! app()->isProduction();
    }

    public function handle(AlurPesanan $alurPesanan): int
    {
        $sesi = SesiPembelajaran::query()->find($this->argument('id_sesi'));
        $kehadiran = Kehadiran::tryFrom((string) $this->argument('kehadiran'));

        if ($sesi === null) {
            $this->components->error('Sesi '.$this->argument('id_sesi').' tidak ditemukan.');

            return self::FAILURE;
        }

        if ($kehadiran === null) {
            $this->components->error('Kehadiran harus "hadir" atau "tidak_hadir".');

            return self::FAILURE;
        }

        try {
            // Demo shortcut: unlike the tutor dashboard, this may log a session that has not started yet.
            $alurPesanan->catatKehadiran($sesi, $kehadiran, $this->option('catatan'), periksaWaktu: false);
        } catch (TransisiStatusTidakSah $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $pesanan = $sesi->pesanan()->firstOrFail();
        $this->components->info("Sesi {$sesi->id_sesi}: {$kehadiran->label()}. Pesanan {$pesanan->id_pesanan}: {$pesanan->status->label()}.");

        return self::SUCCESS;
    }
}
