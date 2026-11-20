<?php

namespace Tests\Concerns;

use App\Enums\Kehadiran;
use App\Enums\MetodePembayaran;
use App\Enums\ModeBelajar;
use App\Enums\StatusPembayaran;
use App\Models\AkunPengguna;
use App\Models\Pesanan;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Services\AlurPesanan;
use App\Services\PemrosesPembayaran;

/**
 * Builds orders through the real services, the same way the app does.
 */
trait MembuatPesanan
{
    /**
     * @param  array<string, mixed>  $profil
     */
    protected function tutorSiapDipesan(array $profil = []): Tutor
    {
        return Tutor::factory()->siapDipesan($profil)->create()->load('profil', 'mataPelajaran', 'jadwal');
    }

    /**
     * @param  array<string, mixed>  $ubah
     */
    protected function pesananBaru(?Siswa $siswa = null, ?Tutor $tutor = null, array $ubah = []): Pesanan
    {
        $tutor ??= $this->tutorSiapDipesan();
        $tutor->loadMissing('profil', 'mataPelajaran', 'jadwal');
        $siswa ??= Siswa::factory()->create();
        $jadwal = $tutor->jadwal->first();

        $mulai = now()->addDay()->startOfDay();
        while ($mulai->isoWeekday() !== $jadwal->hari->isoWeekday()) {
            $mulai->addDay();
        }

        return app(AlurPesanan::class)->buat([
            'siswa' => $siswa,
            'tutor' => $tutor,
            'mapel' => $tutor->mataPelajaran->first(),
            'id_jadwal' => $jadwal->id_jadwal,
            'mode' => ModeBelajar::Online,
            'durasi' => 60,
            'tanggal_mulai' => $mulai,
            'jumlah_pertemuan' => 4,
            ...$ubah,
        ], AkunPengguna::query()->findOrFail($siswa->id_akun));
    }

    protected function pesananMenungguPembayaran(?Siswa $siswa = null, ?Tutor $tutor = null, array $ubah = []): Pesanan
    {
        $pesanan = $this->pesananBaru($siswa, $tutor, $ubah);
        app(AlurPesanan::class)->terima($pesanan);

        return $pesanan;
    }

    protected function pesananDibayar(?Siswa $siswa = null, ?Tutor $tutor = null, array $ubah = []): Pesanan
    {
        $pesanan = $this->pesananMenungguPembayaran($siswa, $tutor, $ubah);
        $pemroses = app(PemrosesPembayaran::class);
        $pembayaran = $pemroses->mulai($pesanan, MetodePembayaran::Qris);
        $pemroses->terimaNotifikasi($pembayaran->id_pembayaran, $pesanan->id_pesanan, StatusPembayaran::Berhasil);

        return $pesanan->refresh();
    }

    protected function pesananSelesai(?Siswa $siswa = null, ?Tutor $tutor = null, array $ubah = []): Pesanan
    {
        $pesanan = $this->pesananDibayar($siswa, $tutor, ['jumlah_pertemuan' => 1, ...$ubah]);

        foreach ($pesanan->sesi()->get() as $sesi) {
            app(AlurPesanan::class)->catatKehadiran($sesi, Kehadiran::Hadir);
        }

        return $pesanan->refresh();
    }
}
