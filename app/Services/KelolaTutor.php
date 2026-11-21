<?php

namespace App\Services;

use App\Enums\StatusPesanan;
use App\Exceptions\AksiDitolak;
use App\Models\AkunPengguna;
use App\Models\MataPelajaran;
use App\Models\Tutor;
use Illuminate\Support\Facades\DB;

/**
 * Changes only the admin may make to a tutor: subjects and levels. Recorded
 * in riwayat_verifikasi_tutor and sent to the tutor.
 */
class KelolaTutor
{
    public function __construct(private Pemberitahu $pemberitahu) {}

    /**
     * A subject still used by an active order (waiting or paid) cannot be removed.
     *
     * @param  list<string>  $kodeMapel
     */
    public function ubahMapel(Tutor $tutor, array $kodeMapel, string $alasan, AkunPengguna $admin): void
    {
        DB::transaction(function () use ($tutor, $kodeMapel, $alasan, $admin): void {
            $tutor = Tutor::query()->lockForUpdate()->findOrFail($tutor->id_tutor);
            $lama = $tutor->mataPelajaran()->pluck('mata_pelajaran.kode_mapel')->all();

            $dihapus = array_values(array_diff($lama, $kodeMapel));
            $ditambah = array_values(array_diff($kodeMapel, $lama));

            if ($dihapus === [] && $ditambah === []) {
                throw new AksiDitolak('Tidak ada perubahan mata pelajaran.');
            }

            $dipakai = $tutor->pesanan()
                ->whereIn('kode_mapel', $dihapus)
                ->whereIn('status', StatusPesanan::active())
                ->distinct()
                ->pluck('kode_mapel')
                ->all();

            if ($dipakai !== []) {
                $label = MataPelajaran::query()->whereIn('kode_mapel', $dipakai)->get()->map->label()->join(', ');

                throw new AksiDitolak("{$label} masih dipakai pesanan yang sedang berjalan, jadi belum bisa dihapus. Selesaikan atau batalkan pesanan itu dulu.");
            }

            $tutor->mataPelajaran()->sync($kodeMapel);

            $nama = fn (array $kode): array => MataPelajaran::query()->whereIn('kode_mapel', $kode)->get()->map->label()->all();
            $namaDitambah = $nama($ditambah);
            $namaDihapus = $nama($dihapus);

            $tutor->riwayatVerifikasi()->create([
                'status' => $tutor->status_verifikasi,
                'catatan' => 'Mata pelajaran diubah admin.'
                    .($namaDitambah ? ' Ditambah: '.implode(', ', $namaDitambah).'.' : '')
                    .($namaDihapus ? ' Dihapus: '.implode(', ', $namaDihapus).'.' : '')
                    .' Alasan: '.$alasan,
                'id_akun' => $admin->id_akun,
                'dicatat_pada' => now(),
            ]);

            $this->pemberitahu->mapelDiubah($tutor, $namaDitambah, $namaDihapus, $alasan);
        });
    }
}
