<?php

namespace App\Services;

use App\Enums\Peran;
use App\Enums\StatusPesanan;
use App\Exceptions\AksiDitolak;
use App\Models\AkunPengguna;
use App\Models\Pesanan;
use App\Models\Tutor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * An admin switching a parent or tutor account off and on. Admin accounts
 * are never switched off here. Every change is recorded in riwayat_status_akun.
 */
class KelolaAkun
{
    public function __construct(private AlurPesanan $alurPesanan, private KataSandi $kataSandi) {}

    /**
     * Switches the account off, ends its sessions, and cancels its orders that
     * still wait for the tutor or for payment.
     *
     * @return int the number of orders cancelled
     */
    public function nonaktifkan(AkunPengguna $akun, AkunPengguna $admin, string $alasan): int
    {
        $this->pastikanBukanAdmin($akun);

        $akun = DB::transaction(function () use ($akun, $admin, $alasan): AkunPengguna {
            $akun = AkunPengguna::query()->lockForUpdate()->findOrFail($akun->id_akun);

            if (! $akun->aktif) {
                throw new AksiDitolak("Akun {$akun->nama} sudah nonaktif.");
            }

            $akun->forceFill(['aktif' => false, 'dinonaktifkan_pada' => now(), 'alasan_nonaktif' => $alasan])->save();
            $akun->riwayatStatus()->create(['aktif' => false, 'alasan' => $alasan, 'oleh' => $admin->id_akun, 'dicatat_pada' => now()]);

            return $akun;
        });

        $this->kataSandi->putusSesiLain($akun, null);

        $pihak = $akun->hasPeran(Peran::Tutor) ? 'tutor' : 'ortu';

        return $this->pesananTertunda($akun)
            ->filter(fn (Pesanan $pesanan): bool => $this->alurPesanan->batalkanKarenaAkunNonaktif($pesanan, $pihak, $admin))
            ->count();
    }

    public function aktifkan(AkunPengguna $akun, AkunPengguna $admin, string $alasan): void
    {
        $this->pastikanBukanAdmin($akun);

        DB::transaction(function () use ($akun, $admin, $alasan): void {
            $akun = AkunPengguna::query()->lockForUpdate()->findOrFail($akun->id_akun);

            if ($akun->aktif) {
                throw new AksiDitolak("Akun {$akun->nama} sudah aktif.");
            }

            $akun->forceFill(['aktif' => true, 'dinonaktifkan_pada' => null, 'alasan_nonaktif' => null])->save();
            $akun->riwayatStatus()->create(['aktif' => true, 'alasan' => $alasan, 'oleh' => $admin->id_akun, 'dicatat_pada' => now()]);
        });
    }

    /**
     * Paid orders still running for this account. They are not cancelled
     * automatically; the admin decides, with a cancellation and refund.
     *
     * @return Collection<int, Pesanan>
     */
    public function pesananDibayarBerjalan(AkunPengguna $akun): Collection
    {
        return $this->pesananAkun($akun)->where('status', StatusPesanan::Dibayar)->with(['siswa', 'tutor', 'mapel'])->get();
    }

    /**
     * @return Collection<int, Pesanan>
     */
    private function pesananTertunda(AkunPengguna $akun): Collection
    {
        return $this->pesananAkun($akun)
            ->whereIn('status', [StatusPesanan::MenungguKonfirmasiTutor, StatusPesanan::MenungguPembayaran])
            ->get();
    }

    /**
     * @return Builder<Pesanan>
     */
    private function pesananAkun(AkunPengguna $akun): Builder
    {
        return $akun->hasPeran(Peran::Tutor)
            ? Pesanan::query()->whereIn('id_tutor', Tutor::query()->select('id_tutor')->where('id_akun', $akun->id_akun))
            : Pesanan::query()->milikAkun($akun->id_akun);
    }

    private function pastikanBukanAdmin(AkunPengguna $akun): void
    {
        if ($akun->hasPeran(Peran::Admin)) {
            throw new AksiDitolak('Akun admin tidak bisa dinonaktifkan atau diaktifkan dari halaman ini.');
        }
    }
}
