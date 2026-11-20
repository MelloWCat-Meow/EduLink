<?php

namespace App\Console\Commands;

use App\Exceptions\TransisiStatusTidakSah;
use App\Models\Pesanan;
use App\Services\AlurPesanan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Stands in for the tutor app, which does not exist yet. Local only.
 */
#[Signature('edulink:simulasi-tutor {id_pesanan : Nomor pesanan, mis. ORD-2026-000001} {aksi : terima atau tolak} {--alasan= : Alasan menolak}')]
#[Description('Simulasi tutor menerima atau menolak pesanan (khusus lokal)')]
class SimulasiTutor extends Command
{
    public function isEnabled(): bool
    {
        return ! app()->isProduction();
    }

    public function handle(AlurPesanan $alurPesanan): int
    {
        $pesanan = Pesanan::query()->find($this->argument('id_pesanan'));

        if ($pesanan === null) {
            $this->components->error('Pesanan '.$this->argument('id_pesanan').' tidak ditemukan.');

            return self::FAILURE;
        }

        try {
            match ($this->argument('aksi')) {
                'terima' => $alurPesanan->terima($pesanan),
                'tolak' => $alurPesanan->tolak($pesanan, $this->option('alasan') ? 'Tutor menolak: '.$this->option('alasan') : null),
                default => throw new TransisiStatusTidakSah('Aksi harus "terima" atau "tolak".'),
            };
        } catch (TransisiStatusTidakSah $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Pesanan {$pesanan->id_pesanan} sekarang: {$pesanan->status->label()}.");

        return self::SUCCESS;
    }
}
