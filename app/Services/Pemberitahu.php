<?php

namespace App\Services;

use App\Enums\JenisNotifikasi;
use App\Enums\ModeBelajar;
use App\Enums\Peran;
use App\Enums\StatusJadwalUlang;
use App\Enums\StatusPencairan;
use App\Enums\StatusVerifikasiTutor;
use App\Models\AkunPengguna;
use App\Models\Keluhan;
use App\Models\PencairanDanaTutor;
use App\Models\PengembalianDana;
use App\Models\PermintaanJadwalUlang;
use App\Models\Pesanan;
use App\Models\RatingUlasan;
use App\Models\SesiPembelajaran;
use App\Models\Tutor;
use App\Notifications\Pemberitahuan;
use App\Support\Format;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Who hears about what. The services call this when something happens;
 * sending waits until the surrounding transaction commits, so a change that
 * is rolled back never notifies anyone. Internal tutors without an account
 * simply receive nothing.
 */
class Pemberitahu
{
    public function pesananBaru(Pesanan $pesanan): void
    {
        $pesanan = $this->muat($pesanan);

        $this->kirim($this->akunTutor($pesanan), new Pemberitahuan(
            JenisNotifikasi::PesananBaru,
            'Permintaan pesanan baru',
            "{$pesanan->mapel->label()} untuk {$pesanan->siswa->nama}, {$pesanan->jadwal->label()}. Jawab sebelum ".Format::waktu($pesanan->ditahan_hingga).'.',
            route('tutor.pesanan.show', $pesanan),
        ));
    }

    public function pesananDiterima(Pesanan $pesanan): void
    {
        $pesanan = $this->muat($pesanan);

        $this->kirim($this->wali($pesanan), new Pemberitahuan(
            JenisNotifikasi::PesananDiterima,
            'Pesanan diterima tutor',
            "{$pesanan->tutor->nama} menerima pesanan {$pesanan->id_pesanan}. Bayar sebelum ".Format::waktu($pesanan->ditahan_hingga).' supaya jadwalnya tidak dilepas.',
            route('pesanan.show', $pesanan),
        ));
    }

    public function pesananDitolak(Pesanan $pesanan, string $alasan): void
    {
        $pesanan = $this->muat($pesanan);

        $this->kirim($this->wali($pesanan), new Pemberitahuan(
            JenisNotifikasi::PesananDitolak,
            'Pesanan ditolak tutor',
            "Pesanan {$pesanan->id_pesanan} dengan {$pesanan->tutor->nama} ditolak. {$alasan}",
            route('pesanan.show', $pesanan),
        ));
    }

    /**
     * @param  'tutor'|'ortu'|'admin'  $oleh
     */
    public function pesananDibatalkan(Pesanan $pesanan, string $oleh, string $alasan): void
    {
        $pesanan = $this->muat($pesanan);
        $pesan = "Pesanan {$pesanan->id_pesanan} ({$pesanan->mapel->label()} untuk {$pesanan->siswa->nama}) dibatalkan";

        if ($oleh !== 'ortu') {
            $this->kirim($this->wali($pesanan), new Pemberitahuan(
                JenisNotifikasi::PesananDibatalkan,
                $oleh === 'tutor' ? 'Pesanan dibatalkan tutor' : 'Pesanan dibatalkan admin',
                "{$pesan} oleh {$oleh}. {$alasan}",
                route('pesanan.show', $pesanan),
            ));
        }

        if ($oleh !== 'tutor') {
            $this->kirim($this->akunTutor($pesanan), new Pemberitahuan(
                JenisNotifikasi::PesananDibatalkan,
                $oleh === 'ortu' ? 'Pesanan dibatalkan orang tua' : 'Pesanan dibatalkan admin',
                "{$pesan}. Slot {$pesanan->jadwal->label()} kembali tersedia.",
                route('tutor.pesanan.show', $pesanan),
            ));
        }
    }

    public function pembayaranBerhasil(Pesanan $pesanan): void
    {
        $pesanan = $this->muat($pesanan);
        $mulai = Format::tanggal($pesanan->tanggal_mulai, true);

        $this->kirim($this->wali($pesanan), new Pemberitahuan(
            JenisNotifikasi::PembayaranBerhasil,
            'Pembayaran berhasil, pesanan dikonfirmasi',
            "Pesanan {$pesanan->id_pesanan} sudah dibayar. Sesi pertama dengan {$pesanan->tutor->nama} {$mulai}.",
            route('pesanan.show', $pesanan),
        ));

        $this->kirim($this->akunTutor($pesanan), new Pemberitahuan(
            JenisNotifikasi::PembayaranBerhasil,
            'Pesanan sudah dibayar',
            "Pesanan {$pesanan->id_pesanan} ({$pesanan->mapel->label()} untuk {$pesanan->siswa->nama}) sudah dibayar. Sesi pertama {$mulai}.",
            route('tutor.pesanan.show', $pesanan),
        ));
    }

    public function pesananKedaluwarsa(Pesanan $pesanan, string $sebab): void
    {
        $pesanan = $this->muat($pesanan);

        $this->kirim($this->wali($pesanan), new Pemberitahuan(
            JenisNotifikasi::PesananKedaluwarsa,
            'Pesanan kedaluwarsa',
            "Pesanan {$pesanan->id_pesanan} dengan {$pesanan->tutor->nama} batal otomatis. {$sebab}",
            route('pesanan.show', $pesanan),
        ));

        $this->kirim($this->akunTutor($pesanan), new Pemberitahuan(
            JenisNotifikasi::PesananKedaluwarsa,
            'Pesanan kedaluwarsa',
            "Pesanan {$pesanan->id_pesanan} ({$pesanan->siswa->nama}) batal otomatis. {$sebab}",
            route('tutor.pesanan.show', $pesanan),
        ));
    }

    /**
     * Asks the parent to confirm or dispute; silence confirms it after 48 hours.
     */
    public function sesiDicatat(SesiPembelajaran $sesi): void
    {
        $pesanan = $this->muat($sesi->pesanan()->firstOrFail());
        $catatan = filled($sesi->catatan) ? ' Catatan tutor: '.$sesi->catatan.'.' : '';

        $this->kirim($this->wali($pesanan), new Pemberitahuan(
            JenisNotifikasi::SesiDicatat,
            'Konfirmasi sesi '.Format::tanggalPendek($sesi->waktu),
            "{$pesanan->tutor->nama} mencatat sesi {$pesanan->siswa->nama}: {$sesi->kehadiran->label()}.{$catatan} Konfirmasi kalau sesuai, atau sanggah dengan alasan, sebelum ".Format::waktu($sesi->batasKonfirmasi()).'. Tanpa jawaban, sesi dianggap terkonfirmasi.',
            route('pesanan.show', $pesanan).'#sesi-'.$sesi->id_sesi,
            tambahan: ['id_sesi' => $sesi->id_sesi],
        ));
    }

    public function sesiDikonfirmasi(SesiPembelajaran $sesi, Pesanan $pesanan, bool $otomatis): void
    {
        $pesanan = $this->muat($pesanan);
        $kapan = Format::waktu($sesi->waktu);

        $this->kirim($this->akunTutor($pesanan), new Pemberitahuan(
            JenisNotifikasi::SesiDikonfirmasi,
            $otomatis ? 'Sesi dikonfirmasi otomatis' : 'Sesi dikonfirmasi orang tua',
            "Sesi {$kapan} ({$pesanan->siswa->nama}, pesanan {$pesanan->id_pesanan}) ".($otomatis ? 'dikonfirmasi otomatis karena tidak dijawab dalam 48 jam' : 'dikonfirmasi orang tua').'. Bagian Anda untuk sesi ini masuk ke saldo tersedia.',
            route('tutor.pendapatan.index'),
        ));

        if ($otomatis) {
            $this->kirim($this->wali($pesanan), new Pemberitahuan(
                JenisNotifikasi::SesiDikonfirmasi,
                'Sesi dikonfirmasi otomatis',
                "Sesi {$kapan} dengan {$pesanan->tutor->nama} dikonfirmasi otomatis karena tidak dijawab dalam 48 jam.",
                route('pesanan.show', $pesanan).'#sesi-'.$sesi->id_sesi,
            ));
        }
    }

    public function sesiDisanggah(SesiPembelajaran $sesi, Pesanan $pesanan, Keluhan $keluhan): void
    {
        $pesanan = $this->muat($pesanan);
        $isi = "Wali {$pesanan->siswa->nama} menyanggah sesi ".Format::waktu($sesi->waktu)." pesanan {$pesanan->id_pesanan}. Alasan: {$sesi->alasan_sanggahan}";

        $this->kirim($this->admin(), new Pemberitahuan(JenisNotifikasi::SesiDisanggah, 'Sanggahan sesi perlu diputuskan', $isi, route('admin.keluhan.show', $keluhan)));
        $this->kirim($this->akunTutor($pesanan), new Pemberitahuan(
            JenisNotifikasi::SesiDisanggah,
            'Sesi Anda disanggah orang tua',
            $isi.' Admin akan memutuskan; bagian Anda untuk sesi ini ditahan sampai ada keputusan.',
            route('tutor.pesanan.show', $pesanan),
        ));
    }

    public function sanggahanDiputuskan(SesiPembelajaran $sesi, Pesanan $pesanan, bool $sah): void
    {
        $pesanan = $this->muat($pesanan);
        $judul = $sah ? 'Sanggahan diputuskan: sesi sah' : 'Sanggahan diputuskan: sesi tidak sah';

        $this->kirim($this->wali($pesanan), new Pemberitahuan(
            JenisNotifikasi::SanggahanDiputuskan,
            $judul,
            $sesi->keputusan_admin.($sah ? '' : ' Biaya sesi ini dikembalikan kepada Anda.'),
            route('pesanan.show', $pesanan).'#sesi-'.$sesi->id_sesi,
        ));
        $this->kirim($this->akunTutor($pesanan), new Pemberitahuan(
            JenisNotifikasi::SanggahanDiputuskan,
            $judul,
            $sesi->keputusan_admin.($sah ? ' Bagian Anda untuk sesi ini masuk ke saldo tersedia.' : ' Anda tidak menerima pendapatan untuk sesi ini.'),
            route('tutor.pesanan.show', $pesanan),
        ));
    }

    public function pengingatSesi(SesiPembelajaran $sesi): void
    {
        $pesanan = $this->muat($sesi->pesanan()->firstOrFail());
        $kapan = 'besok, '.Format::waktu($sesi->waktu, true);
        $tautan = $sesi->tautanBerlaku($pesanan);
        $online = $pesanan->mode === ModeBelajar::Online;
        $tempatOrtu = ! $online ? ' Kunjungan rumah.' : ($tautan ? ' Ruang meeting: '.$tautan : ' Tutor belum membagikan tautan meeting.');
        $tempatTutor = ! $online ? ' Kunjungan rumah.' : ($tautan ? ' Ruang meeting: '.$tautan : ' Tautan meeting sesi ini belum diisi. Bagikan dari detail pesanan sebelum sesi dimulai.');

        $this->kirim($this->wali($pesanan), new Pemberitahuan(
            JenisNotifikasi::PengingatSesi,
            'Pengingat: sesi les besok',
            "{$pesanan->mapel->label()} untuk {$pesanan->siswa->nama} dengan {$pesanan->tutor->nama} {$kapan}.{$tempatOrtu}",
            route('pesanan.show', $pesanan),
        ));

        $this->kirim($this->akunTutor($pesanan), new Pemberitahuan(
            JenisNotifikasi::PengingatSesi,
            'Pengingat: Anda mengajar besok',
            "{$pesanan->mapel->label()} untuk {$pesanan->siswa->nama} {$kapan}.{$tempatTutor}",
            route('tutor.pesanan.show', $pesanan),
        ));
    }

    public function ulasanBaru(RatingUlasan $ulasan): void
    {
        $pesanan = $this->muat($ulasan->pesanan()->firstOrFail());

        $this->kirim($this->akunTutor($pesanan), new Pemberitahuan(
            JenisNotifikasi::UlasanBaru,
            "Ulasan baru: {$ulasan->nilai} dari 5",
            "Wali {$pesanan->siswa->nama} mengulas pesanan {$pesanan->id_pesanan}.",
            route('tutor.ulasan.index'),
        ));
    }

    public function keluhanDiperbarui(Keluhan $keluhan): void
    {
        $pengaju = $keluhan->pengaju()->first();

        $this->kirim($pengaju, new Pemberitahuan(
            JenisNotifikasi::KeluhanDiperbarui,
            "Keluhan {$keluhan->id_keluhan}: {$keluhan->status->label()}",
            filled($keluhan->tanggapan_admin) ? 'Tanggapan admin: '.$keluhan->tanggapan_admin : 'Status keluhan Anda berubah.',
            $pengaju?->hasPeran(Peran::Tutor) ? route('tutor.keluhan.index') : route('keluhan.index'),
        ));
    }

    public function pencairanDiperbarui(PencairanDanaTutor $pencairan): void
    {
        $dibayar = $pencairan->status === StatusPencairan::Dibayar;

        $this->kirim($this->akunTutorDari($pencairan->id_tutor), new Pemberitahuan(
            $dibayar ? JenisNotifikasi::PencairanDibayar : JenisNotifikasi::PencairanDisetujui,
            $dibayar ? 'Pencairan sudah ditransfer' : 'Pencairan disetujui',
            "Pengajuan {$pencairan->id_pencairan} sebesar ".Format::rupiah($pencairan->nominal).($dibayar ? ' sudah ditransfer ke rekening Anda.' : ' disetujui dan segera ditransfer.'),
            route('tutor.pendapatan.index'),
        ));
    }

    public function keputusanVerifikasi(Tutor $tutor, StatusVerifikasiTutor $keputusan, Mailable $email): void
    {
        [$judul, $pesan] = match ($keputusan) {
            StatusVerifikasiTutor::Terverifikasi => ['Pendaftaran disetujui', 'Profil Anda sekarang tampil di pencarian EduLink.'],
            StatusVerifikasiTutor::PerluPerbaikan => ['Pendaftaran perlu diperbaiki', 'Buka formulir, perbaiki bagian yang ditandai, lalu kirim lagi.'],
            default => ['Pendaftaran tidak diterima', 'Baca catatan tim akademik di halaman status pendaftaran.'],
        };

        $this->kirim($this->akunTutorDari($tutor->id_tutor), new Pemberitahuan(
            JenisNotifikasi::KeputusanVerifikasi,
            $judul,
            $pesan,
            route('tutor.area'),
            $email,
        ));
    }

    public function pendaftaranTutorBaru(Tutor $tutor): void
    {
        $this->kirim($this->admin(), new Pemberitahuan(
            JenisNotifikasi::PendaftaranTutorBaru,
            'Pendaftaran tutor menunggu verifikasi',
            "{$tutor->nama} ({$tutor->id_tutor}, ".($tutor->isInternal() ? 'internal' : 'eksternal').') mengirim pendaftaran.',
            route('admin.verifikasi.show', $tutor),
        ));
    }

    public function pengajuanPencairanBaru(PencairanDanaTutor $pencairan): void
    {
        $nama = Tutor::query()->whereKey($pencairan->id_tutor)->value('nama');

        $this->kirim($this->admin(), new Pemberitahuan(
            JenisNotifikasi::PengajuanPencairanBaru,
            'Pengajuan pencairan baru',
            "{$nama} mengajukan pencairan ".Format::rupiah($pencairan->nominal)." ({$pencairan->id_pencairan}).",
            route('admin.pencairan.index'),
        ));
    }

    public function jadwalUlangDiajukan(PermintaanJadwalUlang $permintaan, Pesanan $pesanan): void
    {
        $pesanan = $this->muat($pesanan);
        $dariTutor = AkunPengguna::query()->whereKey($permintaan->id_akun)->value('peran') === Peran::Tutor;
        $isi = 'Sesi '.Format::waktu($permintaan->waktu_lama).' diminta pindah ke '.Format::waktu($permintaan->waktu_baru, true).". Alasan: {$permintaan->alasan} Setujui atau tolak sebelum waktu sesi semula.";

        $dariTutor
            ? $this->kirim($this->wali($pesanan), new Pemberitahuan(JenisNotifikasi::JadwalUlangDiajukan, "{$pesanan->tutor->nama} meminta jadwal ulang", $isi, route('pesanan.show', $pesanan)))
            : $this->kirim($this->akunTutor($pesanan), new Pemberitahuan(JenisNotifikasi::JadwalUlangDiajukan, "Wali {$pesanan->siswa->nama} meminta jadwal ulang", $isi, route('tutor.pesanan.show', $pesanan)));
    }

    public function jadwalUlangDijawab(PermintaanJadwalUlang $permintaan, Pesanan $pesanan): void
    {
        $pengaju = AkunPengguna::query()->find($permintaan->id_akun);
        $disetujui = $permintaan->status === StatusJadwalUlang::Disetujui;

        $this->kirim($pengaju, new Pemberitahuan(
            JenisNotifikasi::JadwalUlangDijawab,
            $disetujui ? 'Jadwal ulang disetujui' : 'Jadwal ulang ditolak',
            $disetujui
                ? 'Sesi pindah ke '.Format::waktu($permintaan->waktu_baru, true).'.'
                : 'Sesi tetap '.Format::waktu($permintaan->waktu_lama, true).'. Alasan: '.$permintaan->alasan_penolakan,
            $pengaju?->hasPeran(Peran::Tutor) ? route('tutor.pesanan.show', $pesanan) : route('pesanan.show', $pesanan),
        ));
    }

    public function pengembalianSelesai(PengembalianDana $pengembalian): void
    {
        $pesanan = $this->muat($pengembalian->pesanan()->firstOrFail());

        $this->kirim($this->wali($pesanan), new Pemberitahuan(
            JenisNotifikasi::PengembalianDana,
            'Dana pesanan '.$pesanan->id_pesanan.' sudah dikembalikan',
            Format::rupiah($pengembalian->nominal).' sudah ditransfer kembali. Catatan admin: '.$pengembalian->catatan,
            route('pesanan.show', $pesanan),
        ));
    }

    /**
     * Tells the other side of the order; the switched-off side cannot sign in anyway.
     *
     * @param  'tutor'|'ortu'  $pihak  whose account was switched off
     */
    public function pesananDibatalkanKarenaAkun(Pesanan $pesanan, string $pihak): void
    {
        $pesanan = $this->muat($pesanan);

        $pihak === 'tutor'
            ? $this->kirim($this->wali($pesanan), new Pemberitahuan(
                JenisNotifikasi::PesananDibatalkan,
                'Pesanan dibatalkan otomatis',
                "Pesanan {$pesanan->id_pesanan} dengan {$pesanan->tutor->nama} dibatalkan karena akun tutor dinonaktifkan EduLink. Belum ada pembayaran yang diproses. Silakan cari tutor lain.",
                route('pesanan.show', $pesanan),
            ))
            : $this->kirim($this->akunTutor($pesanan), new Pemberitahuan(
                JenisNotifikasi::PesananDibatalkan,
                'Pesanan dibatalkan otomatis',
                "Pesanan {$pesanan->id_pesanan} ({$pesanan->siswa->nama}) dibatalkan karena akun orang tua dinonaktifkan EduLink. Slot {$pesanan->jadwal->label()} kembali tersedia.",
                route('tutor.pesanan.show', $pesanan),
            ));
    }

    /**
     * @param  list<string>  $ditambah
     * @param  list<string>  $dihapus
     */
    public function mapelDiubah(Tutor $tutor, array $ditambah, array $dihapus, string $alasan): void
    {
        $perubahan = trim(($ditambah ? 'Ditambah: '.implode(', ', $ditambah).'. ' : '').($dihapus ? 'Dihapus: '.implode(', ', $dihapus).'. ' : ''));

        $this->kirim($this->akunTutorDari($tutor->id_tutor), new Pemberitahuan(
            JenisNotifikasi::MapelDiubah,
            'Mata pelajaran Anda diubah admin',
            "{$perubahan} Alasan: {$alasan}",
            route('tutor.profil.edit'),
        ));
    }

    /**
     * Security notice; also by email so the owner hears of it even if someone
     * else changed it.
     */
    public function kataSandiDiubah(AkunPengguna $akun): void
    {
        $this->kirim($akun, new Pemberitahuan(
            JenisNotifikasi::KataSandiDiubah,
            'Kata sandi Anda baru saja diubah',
            'Kata sandi akun EduLink Anda diubah pada '.Format::waktu(now()).'. Perangkat lain sudah dikeluarkan. Kalau bukan Anda yang mengubahnya, segera atur ulang kata sandi dan hubungi admin EduLink.',
            route('login'),
        ));
    }

    /**
     * @param  AkunPengguna|Collection<int, AkunPengguna>|null  $penerima
     */
    private function kirim(AkunPengguna|Collection|null $penerima, Pemberitahuan $pemberitahuan): void
    {
        $daftar = collect($penerima instanceof AkunPengguna ? [$penerima] : ($penerima ?? []))->filter()->values();

        if ($daftar->isEmpty()) {
            return;
        }

        DB::afterCommit(fn () => Notification::send($daftar, $pemberitahuan));
    }

    private function muat(Pesanan $pesanan): Pesanan
    {
        return $pesanan->loadMissing(['siswa', 'tutor', 'mapel', 'jadwal']);
    }

    private function wali(Pesanan $pesanan): ?AkunPengguna
    {
        return AkunPengguna::query()->find($pesanan->siswa->id_akun);
    }

    private function akunTutor(Pesanan $pesanan): ?AkunPengguna
    {
        return $this->akunTutorDari($pesanan->id_tutor);
    }

    private function akunTutorDari(string $idTutor): ?AkunPengguna
    {
        $idAkun = Tutor::query()->whereKey($idTutor)->value('id_akun');

        return $idAkun === null ? null : AkunPengguna::query()->find($idAkun);
    }

    /**
     * @return Collection<int, AkunPengguna>
     */
    private function admin(): Collection
    {
        return AkunPengguna::query()->where('peran', Peran::Admin)->get();
    }
}
