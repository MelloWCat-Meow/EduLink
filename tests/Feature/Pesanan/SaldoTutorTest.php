<?php

namespace Tests\Feature\Pesanan;

use App\Enums\StatusPencairan;
use App\Models\AkunPengguna;
use App\Models\Pesanan;
use App\Models\SesiPembelajaran;
use App\Services\AlurPesanan;
use App\Services\PencairanDana;
use App\Services\SaldoTutor;
use App\Support\PembagianSesi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class SaldoTutorTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    public function test_balance_counts_confirmed_sessions_and_holds_the_rest(): void
    {
        $tutor = $this->tutorSiapDipesan(['tarif' => '100000.00']);
        $tutor->jadwal()->create(['hari' => 'sabtu', 'jam' => '08:00:00', 'status' => 'tersedia']);
        $jadwal = $tutor->jadwal()->orderBy('id_jadwal')->pluck('id_jadwal');

        // Rp400.000 for 4 sessions, 15%: per session Rp100.000, commission Rp15.000, tutor's share Rp85.000.
        // Two confirmed, one waiting for the parent, one not held yet.
        $berjalan = $this->pesananDibayar(tutor: $tutor, ubah: ['id_jadwal' => $jadwal[0]]);
        $this->catatDanKonfirmasi($berjalan, 2);
        $this->catatDanKonfirmasi($berjalan, 1, konfirmasi: false);

        // Cancelled by the admin after one confirmed session: the other three go back to the parent.
        $dibatalkan = $this->pesananDibayar(tutor: $tutor, ubah: ['id_jadwal' => $jadwal[1]]);
        $this->catatDanKonfirmasi($dibatalkan, 1);
        app(AlurPesanan::class)->batalkanOlehAdmin($dibatalkan, 'Siswa pindah kota.', AkunPengguna::factory()->admin()->create());

        foreach ([[StatusPencairan::Dibayar, 100000], [StatusPencairan::Disetujui, 50000], [StatusPencairan::Menunggu, 20000]] as [$status, $nominal]) {
            $pencairan = $tutor->pencairan()->make(['periode' => '2026-10', 'nominal' => $nominal]);
            $pencairan->status = $status;
            $pencairan->save();
        }

        $this->assertSame([
            'pendapatan' => '300000.00',
            'komisi' => '45000.00',
            'bagian' => '255000.00',
            'ditahan' => '170000.00',
            'dicairkan' => '150000.00',
            'saldo' => '105000.00',
        ], app(SaldoTutor::class)->satu($tutor->id_tutor));

        $rincian = app(SaldoTutor::class)->rincian($tutor->id_tutor)->keyBy(fn (array $baris) => $baris['pesanan']->id_pesanan);
        $this->assertSame([2, 2, 0, 0], [$rincian[$berjalan->id_pesanan]['sesi_tersedia'], $rincian[$berjalan->id_pesanan]['sesi_ditahan'], $rincian[$berjalan->id_pesanan]['sesi_tidak_sah'], $rincian[$berjalan->id_pesanan]['sesi_dikembalikan']]);
        $this->assertSame([1, 0, 3, '85000.00', '300000.00'], [$rincian[$dibatalkan->id_pesanan]['sesi_tersedia'], $rincian[$dibatalkan->id_pesanan]['sesi_ditahan'], $rincian[$dibatalkan->id_pesanan]['sesi_dikembalikan'], $rincian[$dibatalkan->id_pesanan]['tersedia'], $rincian[$dibatalkan->id_pesanan]['dikembalikan']]);
    }

    public function test_per_session_split_adds_up_exactly_to_the_order_total_and_commission(): void
    {
        // Rp99.999,99 over 3 sessions, commission Rp15.000.
        $pesanan = new Pesanan(['total_biaya' => '99999.99']);
        $sesi = collect(['SES0010', 'SES0009', 'SES0011'])->map(fn (string $id) => (new SesiPembelajaran)->forceFill(['id_sesi' => $id]));

        $bagian = PembagianSesi::untuk($pesanan, $sesi, '15000.00');

        $this->assertSame(['SES0009', 'SES0010', 'SES0011'], array_keys($bagian));
        $this->assertSame(['33333.00', '33333.00', '33333.99'], array_column($bagian, 'nilai'));
        $this->assertSame(['5000.00', '5000.00', '5000.00'], array_column($bagian, 'komisi'));
        $this->assertSame('99999.99', array_reduce(array_column($bagian, 'nilai'), fn (string $a, string $b) => bcadd($a, $b, 2), '0'));
        $this->assertSame('84999.99', array_reduce(array_column($bagian, 'bagian'), fn (string $a, string $b) => bcadd($a, $b, 2), '0'));

        // Commission that does not divide evenly: the last session takes the remainder.
        $bagian = PembagianSesi::untuk(new Pesanan(['total_biaya' => '100000.00']), $sesi, '15000.01');
        $this->assertSame(['5000.00', '5000.00', '5000.01'], array_column($bagian, 'komisi'));
    }

    public function test_a_payout_cannot_exceed_the_available_balance_even_with_money_held(): void
    {
        $tutor = $this->tutorSiapDipesan(['tarif' => '100000.00']);
        $tutor->forceFill(['rekening_bank' => 'BRI', 'rekening_nomor' => '1234567890', 'rekening_atas_nama' => 'Tutor'])->save();
        $pesanan = $this->pesananDibayar(tutor: $tutor);
        $this->catatDanKonfirmasi($pesanan, 1);

        // Rp85.000 available, Rp255.000 held.
        $this->expectExceptionMessage('Nominal melebihi saldo tersedia (Rp85.000).');
        app(PencairanDana::class)->ajukan($tutor, now()->format('Y-m'), '85001');
    }

    public function test_tutor_without_orders_has_zero_balance(): void
    {
        $tutor = $this->tutorSiapDipesan();

        $this->assertSame('0.00', app(SaldoTutor::class)->satu($tutor->id_tutor)['saldo']);
    }
}
