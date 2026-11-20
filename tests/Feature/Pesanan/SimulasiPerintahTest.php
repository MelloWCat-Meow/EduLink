<?php

namespace Tests\Feature\Pesanan;

use App\Enums\StatusPesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class SimulasiPerintahTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    public function test_tutor_simulation_accepts_an_order(): void
    {
        $pesanan = $this->pesananBaru();

        $this->artisan('edulink:simulasi-tutor', ['id_pesanan' => $pesanan->id_pesanan, 'aksi' => 'terima'])
            ->expectsOutputToContain('Menunggu pembayaran')
            ->assertSuccessful();

        $this->assertSame(StatusPesanan::MenungguPembayaran, $pesanan->refresh()->status);
    }

    public function test_tutor_simulation_rejects_with_a_reason(): void
    {
        $pesanan = $this->pesananBaru();

        $this->artisan('edulink:simulasi-tutor', ['id_pesanan' => $pesanan->id_pesanan, 'aksi' => 'tolak', '--alasan' => 'Sedang ujian.'])->assertSuccessful();

        $this->assertSame(StatusPesanan::Ditolak, $pesanan->refresh()->status);
        $this->assertSame('Tutor menolak: Sedang ujian.', $pesanan->riwayat()->latest('id')->value('catatan'));
    }

    public function test_tutor_simulation_reports_a_refused_transition(): void
    {
        $pesanan = $this->pesananDibayar();

        $this->artisan('edulink:simulasi-tutor', ['id_pesanan' => $pesanan->id_pesanan, 'aksi' => 'tolak'])
            ->expectsOutputToContain('tidak bisa diubah')
            ->assertFailed();
    }

    public function test_session_simulation_logs_attendance(): void
    {
        $pesanan = $this->pesananDibayar(ubah: ['jumlah_pertemuan' => 1]);
        $sesi = $pesanan->sesi()->firstOrFail();

        $this->artisan('edulink:simulasi-sesi', ['id_sesi' => $sesi->id_sesi, 'kehadiran' => 'hadir', '--catatan' => 'Bab pecahan.'])
            ->expectsOutputToContain('Selesai')
            ->assertSuccessful();

        $this->assertSame('hadir', $sesi->refresh()->kehadiran->value);
        $this->assertSame(StatusPesanan::Selesai, $pesanan->refresh()->status);
    }

    public function test_session_simulation_rejects_an_unknown_attendance_value(): void
    {
        $sesi = $this->pesananDibayar()->sesi()->firstOrFail();

        $this->artisan('edulink:simulasi-sesi', ['id_sesi' => $sesi->id_sesi, 'kehadiran' => 'izin'])->assertFailed();

        $this->assertNull($sesi->refresh()->kehadiran);
    }
}
