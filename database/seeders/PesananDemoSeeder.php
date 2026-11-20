<?php

namespace Database\Seeders;

use App\Enums\Hari;
use App\Enums\JenisKeluhan;
use App\Enums\Kehadiran;
use App\Enums\MetodePembayaran;
use App\Enums\ModeBelajar;
use App\Enums\StatusKeluhan;
use App\Enums\StatusPembayaran;
use App\Enums\StatusPencairan;
use App\Models\AkunPengguna;
use App\Models\JadwalKetersediaan;
use App\Models\Keluhan;
use App\Models\MataPelajaran;
use App\Models\Pengaturan;
use App\Models\Pesanan;
use App\Models\RatingUlasan;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Services\AlurPesanan;
use App\Services\PemrosesPembayaran;
use Closure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Orders in every status for the seeded parents, built through the same
 * services the app uses, with the clock moved back so the history looks
 * real: payments, commission (12% before the rate change, 15% after),
 * sessions, reviews, complaints and payout requests.
 */
class PesananDemoSeeder extends Seeder
{
    private Carbon $sekarang;

    public function __construct(
        private AlurPesanan $alurPesanan,
        private PemrosesPembayaran $pemrosesPembayaran,
    ) {}

    public function run(): void
    {
        $this->sekarang = now()->startOfMinute();

        $siti = AkunPengguna::query()->where('kontak', 'siti.aminah@edulink.test')->firstOrFail();
        $budi = AkunPengguna::query()->where('kontak', 'budi.santoso@edulink.test')->firstOrFail();

        $nadia = Siswa::query()->where('nama', 'Nadia Putri')->firstOrFail();
        $raka = Siswa::query()->where('nama', 'Raka Pratama')->firstOrFail();
        $dimas = Siswa::query()->where('nama', 'Dimas Saputra')->firstOrFail();

        $this->pada($this->hariLalu(70), fn () => Pengaturan::simpan(Pengaturan::KOMISI_PERSEN, '12'));

        // Finished with a review.
        $fisika = $this->pesan($dimas, $budi, 'Fadli Ramadhan', 'FIS-SMA', Hari::Selasa, ModeBelajar::Online, 90, 4, $this->hariLalu(60));
        $this->jalankanSampaiSelesai($fisika);
        $this->ulas($fisika, 5, 'Penjelasannya runtut dan Dimas jadi berani mengerjakan soal cerita sendiri.');

        $ipa = $this->pesan($raka, $siti, 'Rina Marlina', 'IPA-SMP', Hari::Rabu, ModeBelajar::Online, 60, 4, $this->hariLalu(50));
        $this->jalankanSampaiSelesai($ipa);
        $this->ulas($ipa, 5, 'Sabar menjelaskan ulang. Nilai ulangan IPA Raka naik.');

        $calistung = $this->pesan($nadia, $siti, 'Sari Wulandari', 'CAL-SD', Hari::Senin, ModeBelajar::KunjunganRumah, 60, 4, $this->hariLalu(45));
        $this->jalankanSampaiSelesai($calistung);
        $this->ulas($calistung, 4, 'Datang tepat waktu dan membawa kartu huruf sendiri. Satu sesi sempat diundur.');

        // The admin raised the commission; orders paid from here on use 15%.
        $this->pada($this->hariLalu(42), fn () => Pengaturan::simpan(Pengaturan::KOMISI_PERSEN, '15'));

        // Finished, not reviewed yet.
        $matematikaSd = $this->pesan($nadia, $siti, 'Rina Marlina', 'MTK-SD', Hari::Senin, ModeBelajar::KunjunganRumah, 60, 4, $this->hariLalu(40));
        $this->jalankanSampaiSelesai($matematikaSd);

        // Finished, with a review the admin hid, and a resolved complaint.
        $matematikaSma = $this->pesan($dimas, $budi, 'Yusuf Hidayat', 'MTK-SMA', Hari::Rabu, ModeBelajar::Online, 60, 4, $this->hariLalu(35));
        $this->jalankanSampaiSelesai($matematikaSma);
        $ulasan = $this->ulas($matematikaSma, 2, 'Kurang cocok. Kalau mau lanjut hubungi saja langsung beliau di 0812 6610 0205, lebih murah.');
        $ulasan->forceFill(['ditampilkan' => false, 'alasan_moderasi' => 'Memuat nomor telepon pribadi dan ajakan bertransaksi di luar EduLink.'])->save();
        $this->keluhan($matematikaSma, $budi, JenisKeluhan::KualitasPengajaran, 'Dua sesi terakhir lebih banyak mengerjakan soal tanpa pembahasan.', $this->hariLalu(14),
            StatusKeluhan::Selesai, 'Kami sudah bicara dengan Pak Yusuf. Untuk pesanan berikutnya, setiap sesi akan diakhiri pembahasan soal yang salah.');

        // Cancelled by the parent before the tutor answered.
        $biologi = $this->pesan($dimas, $budi, 'Dewi Anggraini', 'BIO-SMA', Hari::Senin, ModeBelajar::KunjunganRumah, 60, 4, $this->hariLalu(20));
        $this->pada($this->hariLalu(20)->addHours(3), fn () => $this->alurPesanan->batalkanOlehOrtu($biologi, $budi));

        // Rejected by the tutor.
        $utbk = $this->pesan($dimas, $budi, 'Fadli Ramadhan', 'UTBK-SMA', Hari::Minggu, ModeBelajar::Online, 120, 8, $this->hariLalu(15));
        $this->pada($this->hariLalu(15)->addHours(5), fn () => $this->alurPesanan->tolak($utbk, 'Tutor menolak: kelas Minggu sudah penuh sampai akhir semester.'));

        // Paid, one session done, the rest upcoming, with an open complaint.
        $inggris = $this->pesan($raka, $siti, 'Andi Pratama', 'BIG-SMP', Hari::Selasa, ModeBelajar::Online, 60, 6, $this->hariLalu(10));
        $this->terimaDanBayar($inggris, MetodePembayaran::Qris);
        $this->hadiriSesiYangLewat($inggris);
        $this->keluhan($inggris, $siti, JenisKeluhan::TutorTidakHadir, 'Pada sesi pertama ruang meeting baru dibuka 15 menit setelah jadwal.', $this->hariLalu(2), StatusKeluhan::Baru);

        // Never answered by the tutor: cancelled when the hold expired.
        $this->pesan($nadia, $siti, 'Maya Handayani', 'IPS-SD', Hari::Selasa, ModeBelajar::KunjunganRumah, 60, 4, $this->hariLalu(5));

        // Accepted, waiting for payment, after one failed attempt.
        $matematikaSmp = $this->pesan($raka, $siti, 'Rina Marlina', 'MTK-SMP', Hari::Sabtu, ModeBelajar::Online, 90, 4, $this->sekarang->copy()->subHours(3));
        $this->pada($this->sekarang->copy()->subHours(2), fn () => $this->alurPesanan->terima($matematikaSmp));
        $this->pada($this->sekarang->copy()->subHour(), function () use ($matematikaSmp): void {
            $pembayaran = $this->pemrosesPembayaran->mulai($matematikaSmp, MetodePembayaran::VirtualAccount);
            $this->pemrosesPembayaran->terimaNotifikasi($pembayaran->id_pembayaran, $matematikaSmp->id_pesanan, StatusPembayaran::Gagal);
        });

        // Just placed, waiting for the tutor.
        $this->pesan($nadia, $siti, 'Sari Wulandari', 'BIN-SD', Hari::Kamis, ModeBelajar::KunjunganRumah, 60, 8, $this->sekarang->copy()->subMinutes(40));

        $this->alurPesanan->lepasPenahananKedaluwarsa();

        $this->pencairan('Fadli Ramadhan', $this->hariLalu(25), 500000, StatusPencairan::Dibayar);
        $this->pencairan('Rina Marlina', $this->hariLalu(1), 400000, StatusPencairan::Menunggu);

        Carbon::setTestNow();
    }

    private function pesan(Siswa $siswa, AkunPengguna $wali, string $namaTutor, string $kodeMapel, Hari $hari, ModeBelajar $mode, int $durasi, int $jumlahPertemuan, Carbon $dibuat): Pesanan
    {
        $tutor = Tutor::query()->with('profil')->where('nama', $namaTutor)->firstOrFail();
        $jadwal = JadwalKetersediaan::query()->where('id_tutor', $tutor->id_tutor)->where('hari', $hari)->firstOrFail();
        $mulai = $dibuat->copy()->addDays(2)->startOfDay();

        while ($mulai->isoWeekday() !== $hari->isoWeekday()) {
            $mulai->addDay();
        }

        return $this->pada($dibuat, fn () => $this->alurPesanan->buat([
            'siswa' => $siswa,
            'tutor' => $tutor,
            'mapel' => MataPelajaran::query()->findOrFail($kodeMapel),
            'id_jadwal' => $jadwal->id_jadwal,
            'mode' => $mode,
            'durasi' => $durasi,
            'tanggal_mulai' => $mulai,
            'jumlah_pertemuan' => $jumlahPertemuan,
        ], $wali));
    }

    private function terimaDanBayar(Pesanan $pesanan, MetodePembayaran $metode): void
    {
        $this->pada($pesanan->created_at->copy()->addHours(4), fn () => $this->alurPesanan->terima($pesanan));

        $this->pada($pesanan->created_at->copy()->addHours(7), function () use ($pesanan, $metode): void {
            $pembayaran = $this->pemrosesPembayaran->mulai($pesanan, $metode);
            $this->pemrosesPembayaran->terimaNotifikasi($pembayaran->id_pembayaran, $pesanan->id_pesanan, StatusPembayaran::Berhasil);
        });
    }

    private function jalankanSampaiSelesai(Pesanan $pesanan): void
    {
        $this->terimaDanBayar($pesanan, MetodePembayaran::VirtualAccount);
        $this->hadiriSesiYangLewat($pesanan);
    }

    private function hadiriSesiYangLewat(Pesanan $pesanan): void
    {
        $pesanan->sesi()->orderBy('waktu')->get()
            ->filter(fn ($sesi) => $sesi->waktu->copy()->addHours(2)->lessThan($this->sekarang))
            ->each(fn ($sesi) => $this->pada($sesi->waktu->copy()->addHours(2), fn () => $this->alurPesanan->catatKehadiran($sesi, Kehadiran::Hadir, 'Materi sesuai rencana.')));
    }

    private function ulas(Pesanan $pesanan, int $nilai, string $komentar): RatingUlasan
    {
        $selesai = $pesanan->riwayat()->latest('dicatat_pada')->value('dicatat_pada');

        return $this->pada(Carbon::parse($selesai)->addDay(), fn () => $pesanan->ulasan()->create(['nilai' => $nilai, 'komentar' => $komentar]));
    }

    private function keluhan(Pesanan $pesanan, AkunPengguna $pengaju, JenisKeluhan $jenis, string $deskripsi, Carbon $waktu, StatusKeluhan $status, ?string $tanggapan = null): void
    {
        $this->pada($waktu, function () use ($pesanan, $pengaju, $jenis, $deskripsi, $status, $tanggapan): void {
            $keluhan = new Keluhan(['id_pesanan' => $pesanan->id_pesanan, 'jenis' => $jenis, 'deskripsi' => $deskripsi]);
            $keluhan->id_akun = $pengaju->id_akun;
            $keluhan->status = $status;
            $keluhan->tanggapan_admin = $tanggapan;
            $keluhan->save();
        });
    }

    private function pencairan(string $namaTutor, Carbon $waktu, int $nominal, StatusPencairan $status): void
    {
        $tutor = Tutor::query()->where('nama', $namaTutor)->firstOrFail();

        $this->pada($waktu, function () use ($tutor, $nominal, $status): void {
            $pencairan = $tutor->pencairan()->make(['periode' => now()->subMonth()->format('Y-m'), 'nominal' => $nominal]);
            $pencairan->status = $status;
            $pencairan->save();
        });
    }

    private function hariLalu(int $hari): Carbon
    {
        return $this->sekarang->copy()->subDays($hari)->setTime(9, 30);
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    private function pada(Carbon $waktu, Closure $callback): mixed
    {
        Carbon::setTestNow($waktu);

        try {
            return $callback();
        } finally {
            Carbon::setTestNow($this->sekarang);
        }
    }
}
