<?php

namespace App\Console\Commands;

use App\Services\JadwalUlang;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('edulink:jadwal-ulang-kedaluwarsa')]
#[Description('Tandai pengajuan jadwal ulang yang tidak dijawab sampai waktu sesi semula sebagai kedaluwarsa')]
class KedaluwarsakanJadwalUlang extends Command
{
    public function handle(JadwalUlang $jadwalUlang): int
    {
        $this->components->info($jadwalUlang->kedaluwarsakan().' pengajuan jadwal ulang kedaluwarsa.');

        return self::SUCCESS;
    }
}
