<?php

namespace App\Services;

use App\Enums\Peran;
use App\Enums\StatusJadwalUlang;
use App\Enums\StatusPesanan;
use App\Exceptions\AksiDitolak;
use App\Models\AkunPengguna;
use App\Models\PermintaanJadwalUlang;
use App\Models\Pesanan;
use App\Models\SesiPembelajaran;
use App\Support\Format;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Moving one session of a paid order. Either side asks, the other side
 * answers. Every rule lives here, and each change runs with the order row
 * locked so two requests cannot race for the same session or time.
 */
class JadwalUlang
{
    /** Hours before the old time after which a request can no longer be made. */
    public const JAM_SEBELUM_SESI = 24;

    /** Hours from now the new time must at least be. */
    public const JAM_MINIMAL_KE_DEPAN = 24;

    /** Days after the old time the new time may at most be. */
    public const HARI_MAKSIMAL_MUNDUR = 14;

    public const MAKS_PER_SESI = 2;

    public function __construct(private Pemberitahu $pemberitahu) {}

    public function ajukan(SesiPembelajaran $sesi, AkunPengguna $oleh, CarbonInterface $waktuBaru, string $alasan): PermintaanJadwalUlang
    {
        $this->kedaluwarsakan();

        return DB::transaction(function () use ($sesi, $oleh, $waktuBaru, $alasan): PermintaanJadwalUlang {
            $pesanan = $this->kunciPesanan($sesi);
            $sesi->refresh();

            if (! $this->pihakPesanan($oleh, $pesanan)) {
                throw new AksiDitolak('Hanya orang tua dan tutor pesanan ini yang bisa mengajukan jadwal ulang.');
            }

            if ($pesanan->status !== StatusPesanan::Dibayar || $sesi->kehadiran !== null) {
                throw new AksiDitolak('Jadwal ulang hanya untuk sesi yang belum tercatat pada pesanan yang sudah dibayar.');
            }

            if (now()->greaterThan($sesi->waktu->copy()->subHours(self::JAM_SEBELUM_SESI))) {
                throw new AksiDitolak('Jadwal ulang paling lambat diajukan '.self::JAM_SEBELUM_SESI.' jam sebelum sesi dimulai.');
            }

            if ($sesi->permintaanJadwalUlang()->where('status', StatusJadwalUlang::Menunggu)->exists()) {
                throw new AksiDitolak('Sesi ini sudah punya pengajuan jadwal ulang yang menunggu jawaban.');
            }

            if ($sesi->permintaanJadwalUlang()->where('status', StatusJadwalUlang::Disetujui)->count() >= self::MAKS_PER_SESI) {
                throw new AksiDitolak('Sesi ini sudah dijadwalkan ulang '.self::MAKS_PER_SESI.' kali, batas maksimalnya.');
            }

            if ($waktuBaru->lessThan(now()->addHours(self::JAM_MINIMAL_KE_DEPAN))) {
                throw new AksiDitolak('Waktu baru paling cepat '.self::JAM_MINIMAL_KE_DEPAN.' jam dari sekarang.');
            }

            if ($waktuBaru->greaterThan($sesi->waktu->copy()->addDays(self::HARI_MAKSIMAL_MUNDUR))) {
                throw new AksiDitolak('Waktu baru paling lambat '.self::HARI_MAKSIMAL_MUNDUR.' hari setelah waktu sesi semula ('.Format::tanggal($sesi->waktu->copy()->addDays(self::HARI_MAKSIMAL_MUNDUR)).').');
            }

            $this->pastikanTidakBentrok($sesi, $pesanan, $waktuBaru);

            $permintaan = $sesi->permintaanJadwalUlang()->create([
                'id_akun' => $oleh->id_akun,
                'waktu_lama' => $sesi->waktu,
                'waktu_baru' => $waktuBaru,
                'alasan' => $alasan,
                'status' => StatusJadwalUlang::Menunggu,
            ]);

            $this->catat($pesanan, $oleh, 'Jadwal ulang diajukan '.($oleh->hasPeran(Peran::Tutor) ? 'tutor' : 'orang tua').': sesi '.Format::waktu($sesi->waktu).' menjadi '.Format::waktu($waktuBaru).'. Alasan: '.$alasan);
            $this->pemberitahu->jadwalUlangDiajukan($permintaan, $pesanan);

            return $permintaan;
        });
    }

    public function setujui(PermintaanJadwalUlang $permintaan, AkunPengguna $oleh): void
    {
        $this->kedaluwarsakan();

        DB::transaction(function () use ($permintaan, $oleh): void {
            [$permintaan, $sesi, $pesanan] = $this->siapDijawab($permintaan, $oleh);

            if ($sesi->kehadiran !== null || $pesanan->status !== StatusPesanan::Dibayar) {
                throw new AksiDitolak('Sesi ini sudah tidak bisa dipindah.');
            }

            $this->pastikanTidakBentrok($sesi, $pesanan, $permintaan->waktu_baru);

            $sesi->update(['waktu' => $permintaan->waktu_baru, 'pengingat_dikirim_pada' => null]);
            $permintaan->update(['status' => StatusJadwalUlang::Disetujui, 'dijawab_pada' => now()]);

            $this->catat($pesanan, $oleh, 'Jadwal ulang disetujui: sesi '.Format::waktu($permintaan->waktu_lama).' pindah ke '.Format::waktu($permintaan->waktu_baru).'.');
            $this->pemberitahu->jadwalUlangDijawab($permintaan, $pesanan);
        });
    }

    public function tolak(PermintaanJadwalUlang $permintaan, AkunPengguna $oleh, string $alasan): void
    {
        $this->kedaluwarsakan();

        DB::transaction(function () use ($permintaan, $oleh, $alasan): void {
            [$permintaan, , $pesanan] = $this->siapDijawab($permintaan, $oleh);

            $permintaan->update(['status' => StatusJadwalUlang::Ditolak, 'dijawab_pada' => now(), 'alasan_penolakan' => $alasan]);

            $this->catat($pesanan, $oleh, 'Jadwal ulang ditolak. Sesi tetap '.Format::waktu($permintaan->waktu_lama).'. Alasan: '.$alasan);
            $this->pemberitahu->jadwalUlangDijawab($permintaan, $pesanan);
        });
    }

    /**
     * Requests nobody answered before the old time: the session stays put.
     * Runs from the scheduler and before every action here.
     *
     * @return int the number of requests that expired
     */
    public function kedaluwarsakan(): int
    {
        return PermintaanJadwalUlang::query()
            ->where('status', StatusJadwalUlang::Menunggu)
            ->where('waktu_lama', '<=', now())
            ->update(['status' => StatusJadwalUlang::Kedaluwarsa, 'updated_at' => now()]);
    }

    public function pihakPesanan(AkunPengguna $akun, Pesanan $pesanan): bool
    {
        return app(KontakWhatsApp::class)->pihakPesanan($akun, $pesanan);
    }

    /**
     * @return array{0: PermintaanJadwalUlang, 1: SesiPembelajaran, 2: Pesanan}
     */
    private function siapDijawab(PermintaanJadwalUlang $permintaan, AkunPengguna $oleh): array
    {
        $sesi = $permintaan->sesi()->firstOrFail();
        $pesanan = $this->kunciPesanan($sesi);
        $permintaan = PermintaanJadwalUlang::query()->lockForUpdate()->findOrFail($permintaan->id);

        if (! $this->pihakPesanan($oleh, $pesanan) || $permintaan->id_akun === $oleh->id_akun) {
            throw new AksiDitolak('Pengajuan ini dijawab oleh pihak lain dalam pesanan, bukan oleh pengajunya.');
        }

        if ($permintaan->status !== StatusJadwalUlang::Menunggu) {
            throw new AksiDitolak('Pengajuan ini sudah tidak menunggu jawaban ('.$permintaan->status->label().').');
        }

        return [$permintaan, $sesi->refresh(), $pesanan];
    }

    private function kunciPesanan(SesiPembelajaran $sesi): Pesanan
    {
        return Pesanan::query()->whereKey($sesi->id_pesanan)->lockForUpdate()->firstOrFail();
    }

    /**
     * The new time may not overlap another session of the same tutor (paid
     * orders, not yet logged), counting each session's own duration.
     */
    private function pastikanTidakBentrok(SesiPembelajaran $sesi, Pesanan $pesanan, CarbonInterface $waktuBaru): void
    {
        $mulai = $waktuBaru->copy();
        $selesai = $waktuBaru->copy()->addMinutes($pesanan->durasi);

        $lain = SesiPembelajaran::query()
            ->where('id_sesi', '!=', $sesi->id_sesi)
            ->whereNull('kehadiran')
            ->whereBetween('waktu', [$mulai->copy()->subDay(), $selesai->copy()->addDay()])
            ->whereHas('pesanan', fn ($query) => $query->where('id_tutor', $pesanan->id_tutor)->where('status', StatusPesanan::Dibayar))
            ->with('pesanan')
            ->get();

        foreach ($lain as $sesiLain) {
            $mulaiLain = $sesiLain->waktu;
            $selesaiLain = $sesiLain->waktu->copy()->addMinutes($sesiLain->pesanan->durasi);

            if ($mulai->lessThan($selesaiLain) && $mulaiLain->lessThan($selesai)) {
                throw new AksiDitolak('Waktu baru bentrok dengan sesi lain tutor pada '.Format::waktu($sesiLain->waktu).'. Pilih waktu lain.');
            }
        }
    }

    private function catat(Pesanan $pesanan, AkunPengguna $oleh, string $catatan): void
    {
        $pesanan->riwayat()->create(['status' => $pesanan->status, 'catatan' => $catatan, 'id_akun' => $oleh->id_akun, 'dicatat_pada' => now()]);
    }
}
