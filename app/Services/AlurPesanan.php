<?php

namespace App\Services;

use App\Enums\Kehadiran;
use App\Enums\ModeBelajar;
use App\Enums\StatusJadwal;
use App\Enums\StatusKonfirmasiSesi;
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

    public function __construct(private Pemberitahu $pemberitahu, private PengembalianSesi $pengembalianSesi) {}

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
     * An admin switched off one side's account: an order still waiting for
     * the tutor or for payment is cancelled and its slot freed. Paid orders
     * are left alone; the admin handles them with a cancellation and refund.
     *
     * @param  'tutor'|'ortu'  $pihak  whose account was switched off
     */
    public function batalkanKarenaAkunNonaktif(Pesanan $pesanan, string $pihak, AkunPengguna $oleh): bool
    {
        return $this->denganKunci($pesanan, function (Pesanan $pesanan) use ($pihak, $oleh): bool {
            if (! $pesanan->status->holdsSlot()) {
                return false;
            }

            $this->pindah($pesanan, StatusPesanan::Batal, 'Dibatalkan otomatis: akun '.($pihak === 'tutor' ? 'tutor' : 'orang tua').' dinonaktifkan admin.', $oleh);
            $this->lepasSlot($pesanan);
            $this->pemberitahu->pesananDibatalkanKarenaAkun($pesanan, $pihak);

            return true;
        });
    }

    /**
     * Admins may cancel any order that is not finished yet, with a recorded
     * reason. A paid order also gets a refund for the sessions not logged.
     */
    public function batalkanOlehAdmin(Pesanan $pesanan, string $alasan, AkunPengguna $oleh): void
    {
        $this->denganKunci($pesanan, function (Pesanan $pesanan) use ($alasan, $oleh): void {
            $sudahDibayar = $pesanan->status === StatusPesanan::Dibayar;

            $this->pindah($pesanan, StatusPesanan::Batal, 'Dibatalkan admin: '.$alasan, $oleh);
            $this->lepasSlot($pesanan);

            $keterangan = 'Alasan: '.$alasan;

            if ($sudahDibayar) {
                // Sessions not logged yet go back to the parent. Logged ones keep
                // going through confirmation; one ruled not valid is refunded then.
                $belumDicatat = $pesanan->sesi()->whereNull('kehadiran')->get();
                $pengembalian = $this->pengembalianSesi->kembalikan($pesanan, $belumDicatat);

                if ($pengembalian !== null) {
                    $keterangan .= ' Dana yang dikembalikan: '.Format::rupiah($pengembalian->nominal).' untuk '.$belumDicatat->count().' sesi yang belum terlaksana.';
                    $this->catatRiwayat($pesanan, 'Pengembalian dana '.Format::rupiah($pengembalian->nominal).' dicatat dan menunggu diproses admin.', $oleh);
                }
            }

            $this->pemberitahu->pesananDibatalkan($pesanan, 'admin', $keterangan);
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
     * The tutor logs a session that took place: held, or held with the student
     * absent without notice. The parent then has 48 hours to confirm or
     * dispute it (KonfirmasiSesi). A session can be logged only once it has
     * started; $periksaWaktu is false only for the demo simulation command
     * and test helpers. A tutor who could not come does not log the session;
     * they ask to reschedule it.
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

            $sesi->fill(['kehadiran' => $kehadiran, 'catatan' => $catatan ?? $sesi->catatan]);
            $sesi->forceFill(['status_konfirmasi' => StatusKonfirmasiSesi::MenungguKonfirmasi, 'dicatat_pada' => now()])->save();

            $this->pemberitahu->sesiDicatat($sesi);
        });
    }

    /**
     * Finishes a paid order once every session is settled (confirmed, confirmed
     * automatically, or ruled not valid), and opens the weekly slot again.
     */
    public function selesaikanJikaTuntas(Pesanan $pesanan): bool
    {
        return $this->denganKunci($pesanan, function (Pesanan $pesanan): bool {
            if ($pesanan->status !== StatusPesanan::Dibayar) {
                return false;
            }

            $belumTuntas = $pesanan->sesi()->get()->contains(fn (SesiPembelajaran $sesi): bool => ! ($sesi->status_konfirmasi?->tuntas() ?? false));

            if ($belumTuntas) {
                return false;
            }

            $this->pindah($pesanan, StatusPesanan::Selesai, 'Semua sesi sudah dikonfirmasi. Pesanan selesai.');
            $this->lepasSlot($pesanan);

            return true;
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
            // Online sessions get their meeting link from the tutor (pesanan or sesi tautan_meeting).
            $pesanan->sesi()->create([
                'waktu' => $pesanan->tanggal_mulai->copy()->addWeeks($minggu)->setTimeFromTimeString($jam),
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
