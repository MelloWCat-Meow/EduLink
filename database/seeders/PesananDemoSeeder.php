<?php

namespace Database\Seeders;

use App\Enums\Hari;
use App\Enums\JenisKeluhan;
use App\Enums\Kehadiran;
use App\Enums\MetodePembayaran;
use App\Enums\ModeBelajar;
use App\Enums\StatusJadwal;
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
use App\Models\SesiPembelajaran;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Services\AlurPesanan;
use App\Services\KonfirmasiSesi;
use App\Services\PemrosesPembayaran;
use Closure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

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
        private KonfirmasiSesi $konfirmasiSesi,
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
        // Finished: three sessions confirmed, the last disputed by the parent and ruled
        // not valid by the admin (the tutor did not turn up), so it was refunded.
        $fisika = $this->pesan($dimas, $budi, 'Fadli Ramadhan', 'FIS-SMA', Hari::Selasa, ModeBelajar::Online, 90, 4, $this->hariLalu(60));
        $this->terimaDanBayar($fisika, MetodePembayaran::VirtualAccount);
        $this->hadiriSesiYangLewat($fisika, belumDikonfirmasi: 1);
        $terakhir = $fisika->sesi()->where('status_konfirmasi', 'menunggu_konfirmasi')->sole();
        $this->pada($terakhir->waktu->copy()->addHours(6), fn () => $this->konfirmasiSesi->sanggah($terakhir, $budi, 'Tutor tidak hadir di ruang meeting sampai sesi berakhir.'));
        $this->pada($terakhir->waktu->copy()->addDay(), fn () => $this->konfirmasiSesi->putuskan($terakhir, $this->admin(), false, 'Tutor mengakui berhalangan dan tidak memberi kabar.'));
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
        // Andi shared his Meet link for the whole order.
        $inggris->forceFill(['tautan_meeting' => 'https://meet.google.com/edu-link-big'])->save();
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

        $this->dasborRina($nadia, $raka, $siti);
        $this->dasborAndi($raka, $dimas, $siti, $budi);
        $this->dasborDewi($raka, $dimas, $siti, $budi);

        $this->alurPesanan->lepasPenahananKedaluwarsa();

        $this->pencairan('Fadli Ramadhan', $this->hariLalu(25), 400000, StatusPencairan::Dibayar);
        $this->pencairan('Rina Marlina', $this->hariLalu(20), 250000, StatusPencairan::Dibayar);
        $this->pencairan('Rina Marlina', $this->hariLalu(1), 300000, StatusPencairan::Menunggu);
        $this->pencairan('Andi Pratama', $this->hariLalu(15), 150000, StatusPencairan::Dibayar);
        $this->pencairan('Dewi Anggraini', $this->hariLalu(5), 150000, StatusPencairan::Disetujui);

        Carbon::setTestNow();
    }

    /**
     * Rina's dashboard: a fresh request to answer, a paid order with one past
     * session still to log, a rejection and a cancellation of her own.
     */
    private function dasborRina(Siswa $nadia, Siswa $raka, AkunPengguna $siti): void
    {
        $rina = $this->akunTutor('Rina Marlina');

        $ditolak = $this->pesan($raka, $siti, 'Rina Marlina', 'MTK-SMP', Hari::Senin, ModeBelajar::KunjunganRumah, 90, 8, $this->hariLalu(30));
        $this->pada($this->hariLalu(30)->addHours(6), fn () => $this->alurPesanan->tolak($ditolak, 'Ditolak tutor: kunjungan ke Kota Baru pada Senin sore belum bisa.', $rina));

        $dibatalkan = $this->pesan($nadia, $siti, 'Rina Marlina', 'IPA-SD', Hari::Senin, ModeBelajar::Online, 60, 4, $this->hariLalu(25));
        $this->pada($this->hariLalu(25)->addHours(2), fn () => $this->alurPesanan->terima($dibatalkan, $rina));
        $this->pada($this->hariLalu(25)->addHours(5), fn () => $this->alurPesanan->batalkanOlehTutor($dibatalkan, 'Jadwal mengajar di sekolah berubah mulai bulan ini.', $rina));

        $berjalan = $this->pesan($nadia, $siti, 'Rina Marlina', 'IPA-SD', Hari::Rabu, ModeBelajar::Online, 60, 6, $this->hariLalu(22));
        $this->terimaDanBayar($berjalan, MetodePembayaran::EWallet);
        // First session confirmed, the second disputed and waiting for the admin, the third not logged yet.
        $this->hadiriSesiYangLewat($berjalan, sisakanTerakhir: true, belumDikonfirmasi: 1);
        $disanggah = $berjalan->sesi()->where('status_konfirmasi', 'menunggu_konfirmasi')->sole();
        $this->pada($disanggah->waktu->copy()->addHours(8), fn () => $this->konfirmasiSesi->sanggah($disanggah, $siti, 'Sesi hanya berlangsung 20 menit, lalu tutor keluar dari ruang meeting.'));

        $this->pesan($nadia, $siti, 'Rina Marlina', 'MTK-SD', Hari::Senin, ModeBelajar::Online, 60, 4, $this->sekarang->copy()->subHours(2));
    }

    /**
     * Andi's dashboard: every status, including one waiting for payment.
     */
    private function dasborAndi(Siswa $raka, Siswa $dimas, AkunPengguna $siti, AkunPengguna $budi): void
    {
        $andi = $this->akunTutor('Andi Pratama');

        $selesai = $this->pesan($dimas, $budi, 'Andi Pratama', 'BIG-SMA', Hari::Jumat, ModeBelajar::Online, 60, 4, $this->hariLalu(60));
        $this->jalankanSampaiSelesai($selesai);
        $this->ulas($selesai, 5, 'Latihan percakapannya seru, Dimas jadi tidak malu bicara.');

        // A session held yesterday, logged by Andi and waiting for the parent, so the
        // "Sesi menunggu konfirmasi Anda" card shows a real countdown.
        $kemarin = Hari::fromIsoWeekday($this->sekarang->copy()->subDay()->isoWeekday());
        Tutor::query()->where('nama', 'Andi Pratama')->firstOrFail()->jadwal()->create(['hari' => $kemarin, 'jam' => '18:00:00', 'status' => 'tersedia']);
        $percakapan = $this->pesan($dimas, $budi, 'Andi Pratama', 'BIG-SMA', $kemarin, ModeBelajar::Online, 60, 4, $this->hariLalu(9), '18:00:00');
        $this->terimaDanBayar($percakapan, MetodePembayaran::Qris);
        $percakapan->forceFill(['tautan_meeting' => 'https://meet.google.com/edu-link-sma'])->save();
        $this->hadiriSesiYangLewat($percakapan, belumDikonfirmasi: 1);

        $ditolak = $this->pesan($raka, $siti, 'Andi Pratama', 'BIG-SMP', Hari::Jumat, ModeBelajar::Online, 120, 8, $this->hariLalu(30));
        $this->pada($this->hariLalu(30)->addHours(3), fn () => $this->alurPesanan->tolak($ditolak, 'Ditolak tutor: durasi 2 jam terlalu panjang untuk kelas malam.', $andi));

        $dibatalkan = $this->pesan($dimas, $budi, 'Andi Pratama', 'BIG-SMA', Hari::Jumat, ModeBelajar::Online, 60, 4, $this->hariLalu(20));
        $this->pada($this->hariLalu(20)->addHours(2), fn () => $this->alurPesanan->terima($dibatalkan, $andi));
        $this->pada($this->hariLalu(20)->addHours(6), fn () => $this->alurPesanan->batalkanOlehTutor($dibatalkan, 'Sedang sakit dan belum bisa mengajar dua minggu ke depan.', $andi));

        $this->pesan($raka, $siti, 'Andi Pratama', 'BIG-SMP', Hari::Jumat, ModeBelajar::Online, 60, 4, $this->sekarang->copy()->subHour());

        $menungguBayar = $this->pesan($dimas, $budi, 'Andi Pratama', 'BIG-SMA', Hari::Sabtu, ModeBelajar::Online, 90, 4, $this->sekarang->copy()->subHours(5));
        $this->pada($this->sekarang->copy()->subHours(4), fn () => $this->alurPesanan->terima($menungguBayar, $andi));
    }

    /**
     * Dewi's dashboard: a paid order with a session tomorrow (for the
     * day-before reminder), plus finished, rejected, waiting orders.
     */
    private function dasborDewi(Siswa $raka, Siswa $dimas, AkunPengguna $siti, AkunPengguna $budi): void
    {
        $dewi = $this->akunTutor('Dewi Anggraini');
        $besok = Hari::fromIsoWeekday($this->sekarang->copy()->addDay()->isoWeekday());
        Tutor::query()->where('nama', 'Dewi Anggraini')->firstOrFail()->jadwal()->create(['hari' => $besok, 'jam' => '17:00:00', 'status' => 'tersedia']);

        $selesai = $this->pesan($raka, $siti, 'Dewi Anggraini', 'IPA-SMP', Hari::Senin, ModeBelajar::Online, 60, 4, $this->hariLalu(50));
        $this->jalankanSampaiSelesai($selesai);
        $this->ulas($selesai, 4, 'Materi IPA dijelaskan dengan percobaan sederhana di rumah.');

        $ditolak = $this->pesan($dimas, $budi, 'Dewi Anggraini', 'BIO-SMA', Hari::Jumat, ModeBelajar::KunjunganRumah, 60, 4, $this->hariLalu(35));
        $this->pada($this->hariLalu(35)->addHours(4), fn () => $this->alurPesanan->tolak($ditolak, 'Ditolak tutor: rumah siswa di luar wilayah kunjungan saya.', $dewi));

        $berjalan = $this->pesan($dimas, $budi, 'Dewi Anggraini', 'BIO-SMA', $besok, ModeBelajar::Online, 60, 4, $this->hariLalu(8), '17:00:00');
        $this->terimaDanBayar($berjalan, MetodePembayaran::VirtualAccount);
        // Dewi uses Zoom for the order, with a different room for tomorrow's session.
        $berjalan->forceFill(['tautan_meeting' => 'https://zoom.us/j/81234567890'])->save();
        $berjalan->sesi()->where('waktu', '>', $this->sekarang)->orderBy('waktu')->first()?->update(['tautan_meeting' => 'https://zoom.us/j/89876543210']);
        // Nobody answered within 48 hours: confirmed automatically.
        $this->hadiriSesiYangLewat($berjalan, belumDikonfirmasi: 1);
        $diam = $berjalan->sesi()->where('status_konfirmasi', 'menunggu_konfirmasi')->sole();
        $this->pada($diam->dicatat_pada->copy()->addHours(49), fn () => $this->konfirmasiSesi->konfirmasiOtomatis());

        $menungguBayar = $this->pesan($raka, $siti, 'Dewi Anggraini', 'IPA-SMP', Hari::Jumat, ModeBelajar::Online, 60, 4, $this->sekarang->copy()->subHours(5));
        $this->pada($this->sekarang->copy()->subHours(3), fn () => $this->alurPesanan->terima($menungguBayar, $dewi));

        $this->pesan($raka, $siti, 'Dewi Anggraini', 'IPA-SMP', Hari::Senin, ModeBelajar::Online, 60, 4, $this->sekarang->copy()->subMinutes(30));
    }

    private function akunTutor(string $nama): AkunPengguna
    {
        return Tutor::query()->with('akun')->where('nama', $nama)->firstOrFail()->akun;
    }

    private function pesan(Siswa $siswa, AkunPengguna $wali, string $namaTutor, string $kodeMapel, Hari $hari, ModeBelajar $mode, int $durasi, int $jumlahPertemuan, Carbon $dibuat, ?string $jam = null): Pesanan
    {
        $tutor = Tutor::query()->with('profil')->where('nama', $namaTutor)->firstOrFail();
        $jadwal = $this->slotTersedia($tutor, $hari, $jam);
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

    /**
     * A free slot of the tutor on that day (and time, when given). Only free
     * slots are picked: a tutor may have two slots on one day, for example the
     * fixed Monday slot and the dynamic "tomorrow" slot when the seeder runs on
     * a Sunday, and one of them may already be held or taken by another order.
     */
    private function slotTersedia(Tutor $tutor, Hari $hari, ?string $jam): JadwalKetersediaan
    {
        $slot = JadwalKetersediaan::query()
            ->where('id_tutor', $tutor->id_tutor)
            ->where('hari', $hari)
            ->where('status', StatusJadwal::Tersedia)
            ->when($jam, fn ($query) => $query->where('jam', $jam))
            ->orderBy('jam')
            ->first();

        if ($slot !== null) {
            return $slot;
        }

        $ada = JadwalKetersediaan::query()->where('id_tutor', $tutor->id_tutor)->where('hari', $hari)->orderBy('jam')->get()
            ->map(fn (JadwalKetersediaan $slot): string => substr((string) $slot->jam, 0, 5).' ('.$slot->status->value.')')
            ->implode(', ');

        throw new RuntimeException(sprintf(
            'PesananDemoSeeder: tidak ada slot tersedia untuk %s pada hari %s%s. Slot hari itu: %s.',
            $tutor->nama,
            $hari->value,
            $jam ? ' jam '.substr($jam, 0, 5) : '',
            $ada !== '' ? $ada : 'tidak ada',
        ));
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

    /**
     * Logs every past session; with $sisakanTerakhir the latest one is left
     * for the tutor to log from the dashboard.
     */
    /**
     * Logs every past session two hours after it started, and has the parent
     * confirm it a few hours later. $sisakanTerakhir leaves the latest past
     * session unlogged; $belumDikonfirmasi leaves that many of the latest
     * logged sessions waiting for the parent.
     */
    private function hadiriSesiYangLewat(Pesanan $pesanan, bool $sisakanTerakhir = false, int $belumDikonfirmasi = 0): void
    {
        $lewat = $pesanan->sesi()->orderBy('waktu')->get()
            ->filter(fn ($sesi) => $sesi->waktu->copy()->addHours(2)->lessThan($this->sekarang))
            ->values();

        $dicatat = $sisakanTerakhir ? $lewat->slice(0, -1)->values() : $lewat;

        $dicatat->each(function ($sesi, int $indeks) use ($pesanan, $dicatat, $belumDikonfirmasi): void {
            $this->pada($sesi->waktu->copy()->addHours(2), fn () => $this->alurPesanan->catatKehadiran($sesi, Kehadiran::Hadir, 'Materi sesuai rencana.'));

            if ($indeks < $dicatat->count() - $belumDikonfirmasi) {
                $this->konfirmasi($pesanan, $sesi);
            }
        });
    }

    private function admin(): AkunPengguna
    {
        return AkunPengguna::query()->where('peran', 'admin')->orderBy('id_akun')->firstOrFail();
    }

    /**
     * The parent confirms a logged session a few hours later, if that moment
     * has already passed; otherwise it stays waiting for confirmation.
     */
    private function konfirmasi(Pesanan $pesanan, SesiPembelajaran $sesi, int $jamKemudian = 5): void
    {
        $pada = $sesi->waktu->copy()->addHours($jamKemudian);

        if ($pada->lessThan($this->sekarang)) {
            $this->pada($pada, fn () => $this->konfirmasiSesi->konfirmasi($sesi, $pesanan->siswa()->firstOrFail()->wali()->firstOrFail()));
        }
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
