<?php

namespace App\Console\Commands;

use App\Services\AlurPesanan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('edulink:lepas-penahanan')]
#[Description('Batalkan pesanan yang batas waktunya lewat dan lepas slot jadwalnya')]
class LepasPenahanan extends Command
{
    public function handle(AlurPesanan $alurPesanan): int
    {
        $jumlah = $alurPesanan->lepasPenahananKedaluwarsa();

        $this->components->info("{$jumlah} pesanan kedaluwarsa dibatalkan.");

        return self::SUCCESS;
    }
}
