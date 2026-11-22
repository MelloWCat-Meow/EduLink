<?php

namespace App\Services;

use App\Enums\JenisKeluhan;
use App\Enums\StatusKeluhan;
use App\Enums\StatusKonfirmasiSesi;
use App\Exceptions\AksiDitolak;
use App\Models\AkunPengguna;
use App\Models\Keluhan;
use App\Models\Pesanan;
use App\Models\SesiPembelajaran;
use App\Support\Format;
use Illuminate\Support\Facades\DB;

/**
 * What happens to a session after the tutor logs it. The parent confirms it
 * or disputes it within 48 hours; silence confirms it automatically. A
 * dispute opens a complaint that the admin settles as valid (the tutor is
 * paid) or not valid (the parent gets that session's value back). Each
 * change runs with the order row locked.
 */
class KonfirmasiSesi
{
    public const BATAS_JAM = 48;

    public function __construct(
        private AlurPesanan $alurPesanan,
        private PengembalianSesi $pengembalianSesi,
        private Pemberitahu $pemberitahu,
    ) {}

    public function konfirmasi(SesiPembelajaran $sesi, AkunPengguna $ortu): void
    {
        $this->konfirmasiOtomatis();

        $pesanan = DB::transaction(function () use ($sesi, $ortu): Pesanan {
            [$sesi, $pesanan] = $this->kunciUntukOrtu($sesi, $ortu);

            $this->tandaiHakTutor($sesi, StatusKonfirmasiSesi::Dikonfirmasi, 'ortu');
            $this->pemberitahu->sesiDikonfirmasi($sesi, $pesanan, otomatis: false);

            return $pesanan;
        });

        $this->alurPesanan->selesaikanJikaTuntas($pesanan);
    }

    public function sanggah(SesiPembelajaran $sesi, AkunPengguna $ortu, string $alasan): Keluhan
    {
        $this->konfirmasiOtomatis();

        return DB::transaction(function () use ($sesi, $ortu, $alasan): Keluhan {
            [$sesi, $pesanan] = $this->kunciUntukOrtu($sesi, $ortu);

            $sesi->forceFill(['status_konfirmasi' => StatusKonfirmasiSesi::Disanggah, 'alasan_sanggahan' => $alasan])->save();

            $keluhan = new Keluhan([
                'id_pesanan' => $pesanan->id_pesanan,
                'jenis' => JenisKeluhan::SesiDisanggah,
                'deskripsi' => 'Sanggahan sesi '.Format::waktu($sesi->waktu).' (dicatat tutor: '.$sesi->kehadiran->label().'). Alasan: '.$alasan,
            ]);
            $keluhan->id_akun = $ortu->id_akun;
            $keluhan->id_sesi = $sesi->id_sesi;
            $keluhan->save();

            $this->pemberitahu->sesiDisanggah($sesi, $pesanan, $keluhan);

            return $keluhan;
        });
    }

    /**
     * Confirms every session still waiting $jam hours after it was logged.
     * Runs hourly from the scheduler and before each action here. Each
     * session is claimed with a conditional update, so it is settled once.
     *
     * @return int the number of sessions confirmed
     */
    public function konfirmasiOtomatis(int $jam = self::BATAS_JAM): int
    {
        $calon = SesiPembelajaran::query()
            ->where('status_konfirmasi', StatusKonfirmasiSesi::MenungguKonfirmasi)
            ->where('dicatat_pada', '<=', now()->subHours($jam))
            ->pluck('id_sesi');

        $jumlah = 0;

        foreach ($calon as $idSesi) {
            $pesanan = DB::transaction(function () use ($idSesi): ?Pesanan {
                $diklaim = SesiPembelajaran::query()->whereKey($idSesi)
                    ->where('status_konfirmasi', StatusKonfirmasiSesi::MenungguKonfirmasi)
                    ->update(['status_konfirmasi' => StatusKonfirmasiSesi::DikonfirmasiOtomatis, 'dikonfirmasi_pada' => now(), 'dikonfirmasi_oleh' => 'sistem']);

                if ($diklaim !== 1) {
                    return null;
                }

                $sesi = SesiPembelajaran::query()->findOrFail($idSesi);
                $pesanan = $sesi->pesanan()->firstOrFail();
                $this->pemberitahu->sesiDikonfirmasi($sesi, $pesanan, otomatis: true);

                return $pesanan;
            });

            if ($pesanan !== null) {
                $jumlah++;
                $this->alurPesanan->selesaikanJikaTuntas($pesanan);
            }
        }

        return $jumlah;
    }

    /**
     * The admin settles a disputed session. Valid: the tutor is paid for it.
     * Not valid: the parent gets the session's value back and the tutor earns
     * nothing for it. The dispute's complaint is closed with the reason.
     */
    public function putuskan(SesiPembelajaran $sesi, AkunPengguna $admin, bool $sah, string $alasan): void
    {
        $pesanan = DB::transaction(function () use ($sesi, $sah, $alasan): Pesanan {
            $pesanan = Pesanan::query()->whereKey($sesi->id_pesanan)->lockForUpdate()->firstOrFail();
            $sesi = SesiPembelajaran::query()->lockForUpdate()->findOrFail($sesi->id_sesi);

            if ($sesi->status_konfirmasi !== StatusKonfirmasiSesi::Disanggah) {
                throw new AksiDitolak('Sesi ini tidak sedang disanggah, jadi tidak perlu diputuskan.');
            }

            $keputusan = ($sah ? 'Sesi sah: ' : 'Sesi tidak sah: ').$alasan;

            if ($sah) {
                $sesi->forceFill(['keputusan_admin' => $keputusan]);
                $this->tandaiHakTutor($sesi, StatusKonfirmasiSesi::Dikonfirmasi, 'admin');
            } else {
                $sesi->forceFill(['status_konfirmasi' => StatusKonfirmasiSesi::TidakSah, 'keputusan_admin' => $keputusan])->save();
                $this->pengembalianSesi->kembalikan($pesanan, collect([$sesi]));
            }

            Keluhan::query()->where('id_sesi', $sesi->id_sesi)->whereIn('status', [StatusKeluhan::Baru, StatusKeluhan::Diproses])
                ->update(['status' => StatusKeluhan::Selesai, 'tanggapan_admin' => $keputusan.($sah ? '' : ' Biaya sesi ini dikembalikan kepada orang tua.'), 'updated_at' => now()]);

            $pesanan->riwayat()->create([
                'status' => $pesanan->status,
                'catatan' => 'Sanggahan sesi '.Format::waktu($sesi->waktu).' diputuskan. '.$keputusan,
                'id_akun' => null,
                'dicatat_pada' => now(),
            ]);

            $this->pemberitahu->sanggahanDiputuskan($sesi->refresh(), $pesanan, $sah);

            return $pesanan;
        });

        $this->alurPesanan->selesaikanJikaTuntas($pesanan);
    }

    /**
     * @return array{0: SesiPembelajaran, 1: Pesanan}
     */
    private function kunciUntukOrtu(SesiPembelajaran $sesi, AkunPengguna $ortu): array
    {
        $pesanan = Pesanan::query()->whereKey($sesi->id_pesanan)->lockForUpdate()->firstOrFail();
        $sesi = SesiPembelajaran::query()->lockForUpdate()->findOrFail($sesi->id_sesi);

        if (! $pesanan->siswa()->where('id_akun', $ortu->id_akun)->exists()) {
            throw new AksiDitolak('Hanya orang tua pesanan ini yang bisa mengonfirmasi atau menyanggah sesinya.');
        }

        if ($sesi->status_konfirmasi !== StatusKonfirmasiSesi::MenungguKonfirmasi) {
            throw new AksiDitolak('Sesi ini tidak sedang menunggu konfirmasi'.($sesi->status_konfirmasi ? ' ('.$sesi->status_konfirmasi->label().').' : '.'));
        }

        return [$sesi, $pesanan];
    }

    private function tandaiHakTutor(SesiPembelajaran $sesi, StatusKonfirmasiSesi $status, string $oleh): void
    {
        $sesi->forceFill(['status_konfirmasi' => $status, 'dikonfirmasi_pada' => now(), 'dikonfirmasi_oleh' => $oleh])->save();
    }
}
