<?php

namespace App\Services;

use App\Enums\Kehadiran;
use App\Enums\ModeBelajar;
use App\Enums\StatusJadwal;
use App\Enums\StatusPesanan;
use App\Exceptions\JadwalTerisi;
use App\Exceptions\TransisiStatusTidakSah;
use App\Models\AkunPengguna;
use App\Models\JadwalKetersediaan;
use App\Models\MataPelajaran;
use App\Models\Pengaturan;
use App\Models\Pesanan;
use App\Models\SesiPembelajaran;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Support\Format;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The one place where an order changes status. Every change runs in a
 * transaction with the order row (and its slot, when touched) locked, checks
 * the move against StatusPesanan::transitions() and writes a timeline entry.
 */
class AlurPesanan
{
    /**
     * Hours a slot stays held while waiting for the tutor, and again for payment.
     */
    public const MASA_TAHAN_JAM = 24;

    public function __construct(private Pemberitahu $pemberitahu) {}

    /**
     * @param  array{siswa: Siswa, tutor: Tutor, mapel: MataPelajaran, id_jadwal: string, mode: ModeBelajar, durasi: int, tanggal_mulai: Carbon, jumlah_pertemuan: int}  $data
     */
    public function buat(array $data, AkunPengguna $oleh): Pesanan
    {
        return DB::transaction(function () use ($data, $oleh): Pesanan {
            $this->lepasPenahananKedaluwarsa($data['id_jadwal']);

            $jadwal = JadwalKetersediaan::query()
                ->whereKey($data['id_jadwal'])
                ->where('id_tutor', $data['tutor']->id_tutor)
                ->lockForUpdate()
                ->firstOrFail();

            if ($jadwal->status !== StatusJadwal::Tersedia) {
                throw new JadwalTerisi;
            }

            $tarif = (string) $data['tutor']->profil->tarif;

            $pesanan = new Pesanan([
                'id_siswa' => $data['siswa']->id_siswa,
                'id_tutor' => $data['tutor']->id_tutor,
                'kode_mapel' => $data['mapel']->kode_mapel,
                'id_jadwal' => $jadwal->id_jadwal,
                'durasi' => $data['durasi'],
                'mode' => $data['mode'],
                'tanggal_mulai' => $data['tanggal_mulai']->toDateString(),
                'jumlah_pertemuan' => $data['jumlah_pertemuan'],
                'tarif_per_jam' => $tarif,
                'total_biaya' => self::hitungTotal($tarif, $data['durasi'], $data['jumlah_pertemuan']),
            ]);
            $pesanan->status = StatusPesanan::MenungguKonfirmasiTutor;
            $pesanan->ditahan_hingga = now()->addHours(self::MASA_TAHAN_JAM);
            $pesanan->save();

            $jadwal->update(['status' => StatusJadwal::Ditahan]);

            $this->catatRiwayat($pesanan, 'Pesanan dibuat. Menunggu tutor menerima.', $oleh);
            $this->pemberitahu->pesananBaru($pesanan);

            return $pesanan;
        });
    }

    /**
     * total = hourly rate x (minutes / 60) x number of meetings, in rupiah with two decimals.
     */
    public static function hitungTotal(string $tarifPerJam, int $durasiMenit, int $jumlahPertemuan): string
    {
        $perPertemuan = bcdiv(bcmul($tarifPerJam, (string) $durasiMenit, 4), '60', 4);

        return self::bulatkan(bcmul($perPertemuan, (string) $jumlahPertemuan, 4));
    }

    public function terima(Pesanan $pesanan, ?AkunPengguna $oleh = null): void
    {
        $this->tolakJikaKedaluwarsa($pesanan);

        $this->denganKunci($pesanan, function (Pesanan $pesanan) use ($oleh): void {
            $this->pindah($pesanan, StatusPesanan::MenungguPembayaran, 'Tutor menerima pesanan. Silakan bayar sebelum batas waktu.', $oleh);
            $pesanan->ditahan_hingga = now()->addHours(self::MASA_TAHAN_JAM);
            $pesanan->save();

            $this->pemberitahu->pesananDiterima($pesanan);
        });
    }

    public function tolak(Pesanan $pesanan, ?string $alasan = null, ?AkunPengguna $oleh = null): void
    {
        $this->tolakJikaKedaluwarsa($pesanan);

        $this->denganKunci($pesanan, function (Pesanan $pesanan) use ($alasan, $oleh): void {
            $this->pindah($pesanan, StatusPesanan::Ditolak, $alasan ?? 'Tutor menolak pesanan.', $oleh);
            $this->lepasSlot($pesanan);
            $this->pemberitahu->pesananDitolak($pesanan, $alasan ?? 'Tutor menolak pesanan.');
        });
    }

    /**
     * Parents may cancel only before the order is paid.
     */
    public function batalkanOlehOrtu(Pesanan $pesanan, AkunPengguna $oleh): void
    {
        $this->denganKunci($pesanan, function (Pesanan $pesanan) use ($oleh): void {
            if (! $pesanan->status->holdsSlot()) {
                throw TransisiStatusTidakSah::dari($pesanan->status, StatusPesanan::Batal);
            }

            $this->pindah($pesanan, StatusPesanan::Batal, 'Dibatalkan oleh orang tua.', $oleh);
            $this->lepasSlot($pesanan);
            $this->pemberitahu->pesananDibatalkan($pesanan, 'ortu', 'Dibatalkan oleh orang tua.');
        });
    }

    /**
     * Tutors may cancel an order they accepted while it still waits for
     * payment. Once paid, a tutor has to file a complaint instead.
     */
    public function batalkanOlehTutor(Pesanan $pesanan, string $alasan, AkunPengguna $oleh): void
    {
        $this->tolakJikaKedaluwarsa($pesanan);

        $this->denganKunci($pesanan, function (Pesanan $pesanan) use ($alasan, $oleh): void {
            if ($pesanan->status !== StatusPesanan::MenungguPembayaran) {
                throw new TransisiStatusTidakSah("Tutor hanya bisa membatalkan pesanan yang menunggu pembayaran. Status pesanan ini: {$pesanan->status->label()}. Untuk pesanan yang sudah dibayar, ajukan keluhan ke admin.");
            }

            $this->pindah($pesanan, StatusPesanan::Batal, 'Dibatalkan tutor: '.$alasan, $oleh);
            $this->lepasSlot($pesanan);
            $this->pemberitahu->pesananDibatalkan($pesanan, 'tutor', 'Alasan: '.$alasan);
        });
    }

    /**
     * Admins may cancel any order that is not finished yet, with a recorded reason.
     */
    public function batalkanOlehAdmin(Pesanan $pesanan, string $alasan, AkunPengguna $oleh): void
    {
        $this->denganKunci($pesanan, function (Pesanan $pesanan) use ($alasan, $oleh): void {
            $this->pindah($pesanan, StatusPesanan::Batal, 'Dibatalkan admin: '.$alasan, $oleh);
            $this->lepasSlot($pesanan);
            $this->pemberitahu->pesananDibatalkan($pesanan, 'admin', 'Alasan: '.$alasan);
        });
    }

    /**
     * Called by the payment service once the gateway reports success.
     * Fills the slot, creates the weekly sessions and fixes the commission.
     */
    public function tandaiDibayar(Pesanan $pesanan): void
    {
        $this->denganKunci($pesanan, function (Pesanan $pesanan): void {
            $this->pindah($pesanan, StatusPesanan::Dibayar, 'Pembayaran berhasil. Jadwal sesi sudah dibuat.');

            $pesanan->ditahan_hingga = null;
            $pesanan->save();

            JadwalKetersediaan::query()->whereKey($pesanan->id_jadwal)->lockForUpdate()->first()
                ?->update(['status' => StatusJadwal::Terisi]);

            $this->buatSesi($pesanan);

            $persen = Pengaturan::komisiPersen();
            $pesanan->komisi()->create([
                'persentase' => $persen,
                'nominal' => self::bulatkan(bcdiv(bcmul((string) $pesanan->total_biaya, $persen, 4), '100', 4)),
            ]);

            $this->pemberitahu->pembayaranBerhasil($pesanan);
        });
    }

    /**
     * The tutor turns down a request; the reason is kept on the timeline.
     */
    public function tolakOlehTutor(Pesanan $pesanan, string $alasan, AkunPengguna $oleh): void
    {
        $this->tolak($pesanan, 'Ditolak tutor: '.$alasan, $oleh);
    }

    /**
     * Logs attendance for one session. When every session of the order has
     * been logged, the order is finished and the weekly slot opens again.
     * A session can be logged only once it has started; $periksaWaktu is
     * false only for the local simulation command and test helpers.
     */
    public function catatKehadiran(SesiPembelajaran $sesi, Kehadiran $kehadiran, ?string $catatan = null, bool $periksaWaktu = true, ?AkunPengguna $oleh = null): void
    {
        $this->denganKunci($sesi->pesanan()->firstOrFail(), function (Pesanan $pesanan) use ($sesi, $kehadiran, $catatan, $periksaWaktu): void {
            if ($pesanan->status !== StatusPesanan::Dibayar) {
                throw new TransisiStatusTidakSah("Kehadiran hanya bisa dicatat untuk pesanan yang sudah dibayar. Status pesanan ini: {$pesanan->status->label()}.");
            }

            $sesi->refresh();

            if ($sesi->kehadiran !== null) {
                throw new TransisiStatusTidakSah('Kehadiran sesi ini sudah dicatat.');
            }

            if ($periksaWaktu && $sesi->waktu->isFuture()) {
                throw new TransisiStatusTidakSah('Kehadiran baru bisa dicatat setelah sesi dimulai ('.Format::waktu($sesi->waktu, true).').');
            }

            $sesi->update(['kehadiran' => $kehadiran, 'catatan' => $catatan ?? $sesi->catatan]);
            $this->pemberitahu->sesiDicatat($sesi);

            if ($pesanan->sesi()->whereNull('kehadiran')->doesntExist()) {
                $this->pindah($pesanan, StatusPesanan::Selesai, 'Semua sesi sudah tercatat. Pesanan selesai.');
                $this->lepasSlot($pesanan);
            }
        });
    }

    public function kedaluwarsakan(Pesanan $pesanan): bool
    {
        return $this->denganKunci($pesanan, function (Pesanan $pesanan): bool {
            if (! $pesanan->isHoldExpired()) {
                return false;
            }

            $this->batalkanKarenaKedaluwarsa($pesanan);

            return true;
        });
    }

    /**
     * Cancels every order whose hold deadline has passed and frees its slot.
     * Runs from the scheduler and before pages that read orders or slots.
     *
     * @return int the number of orders cancelled
     */
    public function lepasPenahananKedaluwarsa(?string $idJadwal = null): int
    {
        $ids = Pesanan::query()
            ->whereIn('status', [StatusPesanan::MenungguKonfirmasiTutor, StatusPesanan::MenungguPembayaran])
            ->where('ditahan_hingga', '<', now())
            ->when($idJadwal, fn ($query) => $query->where('id_jadwal', $idJadwal))
            ->pluck('id_pesanan');

        return $ids->filter(fn (string $id): bool => $this->kedaluwarsakan(Pesanan::query()->findOrFail($id)))->count();
    }

    /**
     * Cancels an expired order in its own transaction (so the cancellation is
     * kept), then refuses the action that was asked for.
     */
    private function tolakJikaKedaluwarsa(Pesanan $pesanan): void
    {
        if ($this->kedaluwarsakan($pesanan)) {
            throw new TransisiStatusTidakSah('Batas waktu pesanan ini sudah lewat, jadi pesanan dibatalkan otomatis.');
        }
    }

    private function batalkanKarenaKedaluwarsa(Pesanan $pesanan): void
    {
        $sebab = $pesanan->status === StatusPesanan::MenungguKonfirmasiTutor
            ? 'Tutor tidak menjawab dalam '.self::MASA_TAHAN_JAM.' jam.'
            : 'Pembayaran tidak diterima dalam '.self::MASA_TAHAN_JAM.' jam.';

        $this->pindah($pesanan, StatusPesanan::Batal, 'Kedaluwarsa. '.$sebab.' Jadwal dilepas.');
        $this->lepasSlot($pesanan);
        $this->pemberitahu->pesananKedaluwarsa($pesanan, $sebab);
    }

    private function pindah(Pesanan $pesanan, StatusPesanan $ke, string $catatan, ?AkunPengguna $oleh = null): void
    {
        if (! $pesanan->status->canMoveTo($ke)) {
            throw TransisiStatusTidakSah::dari($pesanan->status, $ke);
        }

        $pesanan->status = $ke;
        $pesanan->save();

        $this->catatRiwayat($pesanan, $catatan, $oleh);
    }

    private function catatRiwayat(Pesanan $pesanan, string $catatan, ?AkunPengguna $oleh): void
    {
        $pesanan->riwayat()->create([
            'status' => $pesanan->status,
            'catatan' => $catatan,
            'id_akun' => $oleh?->id_akun,
            'dicatat_pada' => now(),
        ]);
    }

    private function lepasSlot(Pesanan $pesanan): void
    {
        $pesanan->ditahan_hingga = null;
        $pesanan->save();

        JadwalKetersediaan::query()->whereKey($pesanan->id_jadwal)->lockForUpdate()->first()
            ?->update(['status' => StatusJadwal::Tersedia]);
    }

    private function buatSesi(Pesanan $pesanan): void
    {
        $jam = $pesanan->jadwal()->value('jam');

        for ($minggu = 0; $minggu < $pesanan->jumlah_pertemuan; $minggu++) {
            $pesanan->sesi()->create([
                'waktu' => $pesanan->tanggal_mulai->copy()->addWeeks($minggu)->setTimeFromTimeString($jam),
                'tautan_meeting' => $pesanan->mode === ModeBelajar::Online
                    ? 'https://meet.edulink.test/'.Str::lower(Str::random(3).'-'.Str::random(4).'-'.Str::random(3))
                    : null,
            ]);
        }
    }

    /**
     * Runs $callback on a freshly locked copy of the order, then copies the
     * new state back onto the caller's instance.
     *
     * @template T
     *
     * @param  Closure(Pesanan): T  $callback
     * @return T
     */
    private function denganKunci(Pesanan $pesanan, Closure $callback): mixed
    {
        return DB::transaction(function () use ($pesanan, $callback): mixed {
            $terkunci = Pesanan::query()->whereKey($pesanan->getKey())->lockForUpdate()->firstOrFail();

            $hasil = $callback($terkunci);

            $pesanan->setRawAttributes($terkunci->getAttributes(), true);

            return $hasil;
        });
    }

    /**
     * Rounds a positive amount half up to whole sen.
     */
    private static function bulatkan(string $angka): string
    {
        return bcadd($angka, '0.005', 2);
    }
}
