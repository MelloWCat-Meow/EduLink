<?php

namespace App\Console\Commands;

use App\Services\KonfirmasiSesi;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('edulink:konfirmasi-otomatis {--setelah=48 : Jam sejak sesi dicatat. Nilai selain 48 hanya boleh di mode demo, untuk mempercepat demo.}')]
#[Description('Konfirmasi otomatis sesi yang tidak dijawab orang tua dalam 48 jam')]
class KonfirmasiSesiOtomatis extends Command
{
    public function handle(KonfirmasiSesi $konfirmasiSesi): int
    {
        $jam = filter_var($this->option('setelah'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        if ($jam === false) {
            $this->components->error('--setelah harus berupa jumlah jam (0 atau lebih).');

            return self::FAILURE;
        }

        if ($jam !== KonfirmasiSesi::BATAS_JAM && ! config('edulink.mode_demo')) {
            $this->components->error('Mengubah batas '.KonfirmasiSesi::BATAS_JAM.' jam hanya boleh di mode demo (EDULINK_MODE_DEMO).');

            return self::FAILURE;
        }

        $jumlah = $konfirmasiSesi->konfirmasiOtomatis($jam);
        $this->components->info("{$jumlah} sesi dikonfirmasi otomatis (dicatat lebih dari {$jam} jam lalu dan belum dijawab).");

        return self::SUCCESS;
    }
}
