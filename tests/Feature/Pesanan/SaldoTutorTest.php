<?php

namespace Tests\Feature\Pesanan;

use App\Enums\StatusPencairan;
use App\Models\AkunPengguna;
use App\Services\AlurPesanan;
use App\Services\SaldoTutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class SaldoTutorTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    public function test_balance_is_successful_payments_minus_commission_minus_earlier_payouts(): void
    {
        $tutor = $this->tutorSiapDipesan(['tarif' => '100000.00']);
        $tutor->jadwal()->create(['hari' => 'sabtu', 'jam' => '08:00:00', 'status' => 'tersedia']);
        $tutor->jadwal()->create(['hari' => 'minggu', 'jam' => '08:00:00', 'status' => 'tersedia']);
        $jadwal = $tutor->jadwal()->orderBy('id_jadwal')->pluck('id_jadwal');

        // 2 x Rp400.000 paid, 15% commission each.
        $this->pesananDibayar(tutor: $tutor, ubah: ['id_jadwal' => $jadwal[0]]);
        $this->pesananDibayar(tutor: $tutor, ubah: ['id_jadwal' => $jadwal[1]]);

        // Paid, then cancelled by the admin: treated as refunded.
        $dibatalkan = $this->pesananDibayar(tutor: $tutor, ubah: ['id_jadwal' => $jadwal[2]]);
        app(AlurPesanan::class)->batalkanOlehAdmin($dibatalkan, 'Pengembalian dana.', AkunPengguna::factory()->admin()->create());

        foreach ([[StatusPencairan::Dibayar, 200000], [StatusPencairan::Disetujui, 100000], [StatusPencairan::Menunggu, 50000]] as [$status, $nominal]) {
            $pencairan = $tutor->pencairan()->make(['periode' => '2026-10', 'nominal' => $nominal]);
            $pencairan->status = $status;
            $pencairan->save();
        }

        $saldo = app(SaldoTutor::class)->satu($tutor->id_tutor);

        $this->assertSame([
            'pendapatan' => '800000.00',
            'komisi' => '120000.00',
            'dicairkan' => '300000.00',
            'saldo' => '380000.00',
        ], $saldo);
    }

    public function test_tutor_without_orders_has_zero_balance(): void
    {
        $tutor = $this->tutorSiapDipesan();

        $this->assertSame('0.00', app(SaldoTutor::class)->satu($tutor->id_tutor)['saldo']);
    }
}
