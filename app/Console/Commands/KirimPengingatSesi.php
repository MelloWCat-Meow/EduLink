<?php

namespace App\Console\Commands;

use App\Enums\StatusPesanan;
use App\Models\SesiPembelajaran;
use App\Services\Pemberitahu;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('edulink:pengingat-sesi')]
#[Description('Kirim pengingat H-1 ke orang tua dan tutor untuk sesi besok')]
class KirimPengingatSesi extends Command
{
    /**
     * Each session is claimed by setting pengingat_dikirim_pada with a
     * conditional update first, so running the command twice (or two
     * servers at once) never sends the same reminder twice.
     */
    public function handle(Pemberitahu $pemberitahu): int
    {
        $besok = now()->addDay();
        $jumlah = 0;

        $calon = SesiPembelajaran::query()
            ->whereNull('pengingat_dikirim_pada')
            ->whereNull('kehadiran')
            ->whereBetween('waktu', [$besok->copy()->startOfDay(), $besok->copy()->endOfDay()])
            ->whereHas('pesanan', fn ($pesanan) => $pesanan->where('status', StatusPesanan::Dibayar))
            ->pluck('id_sesi');

        foreach ($calon as $idSesi) {
            DB::transaction(function () use ($idSesi, $pemberitahu, &$jumlah): void {
                $diklaim = SesiPembelajaran::query()->whereKey($idSesi)->whereNull('pengingat_dikirim_pada')->update(['pengingat_dikirim_pada' => now()]);

                if ($diklaim === 1) {
                    $pemberitahu->pengingatSesi(SesiPembelajaran::query()->findOrFail($idSesi));
                    $jumlah++;
                }
            });
        }

        $this->components->info("{$jumlah} pengingat sesi dikirim.");

        return self::SUCCESS;
    }
}
