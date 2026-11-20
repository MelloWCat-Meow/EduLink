<?php

namespace Tests\Feature\Admin;

use App\Enums\StatusVerifikasi;
use App\Models\AkunPengguna;
use App\Models\Keluhan;
use App\Models\Tutor;
use App\Services\AlurPesanan;
use App\Services\LaporanRingkasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class RingkasanTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    public function test_summary_for_a_month_is_computed_from_the_database(): void
    {
        $tutor = $this->tutorSiapDipesan(['tarif' => '100000.00']);
        $tutor->jadwal()->create(['hari' => 'sabtu', 'jam' => '08:00:00', 'status' => 'tersedia']);
        $tutor->jadwal()->create(['hari' => 'minggu', 'jam' => '08:00:00', 'status' => 'tersedia']);
        $jadwal = $tutor->jadwal()->orderBy('id_jadwal')->pluck('id_jadwal');
        $lain = $this->tutorSiapDipesan();

        // October: one order, paid. Must not leak into November.
        $this->travelTo(Carbon::parse('2026-10-30 10:00'));
        $this->pesananDibayar(tutor: $lain);

        // November 3: two orders for $tutor, one paid (Rp400.000, 15% = Rp60.000), one rejected.
        $this->travelTo(Carbon::parse('2026-11-03 09:00'));
        $this->pesananDibayar(tutor: $tutor, ubah: ['id_jadwal' => $jadwal[0]]);
        app(AlurPesanan::class)->tolak($this->pesananBaru(tutor: $tutor, ubah: ['id_jadwal' => $jadwal[1]]));

        // November 10: one order still waiting for the tutor.
        $this->travelTo(Carbon::parse('2026-11-10 09:00'));
        $menunggu = $this->pesananBaru(tutor: $tutor, ubah: ['id_jadwal' => $jadwal[2]]);

        $keluhan = new Keluhan(['id_pesanan' => $menunggu->id_pesanan, 'jenis' => 'lainnya', 'deskripsi' => 'Belum dijawab tutor.']);
        $keluhan->id_akun = AkunPengguna::factory()->ortu()->create()->id_akun;
        $keluhan->save();
        Tutor::factory()->eksternal(StatusVerifikasi::Menunggu)->menunggu()->create();

        $r = app(LaporanRingkasan::class)->untuk('2026-11');

        $this->assertSame(3, $r['jumlah_pesanan']);
        $this->assertSame(['menunggu_konfirmasi_tutor' => 1, 'ditolak' => 1, 'menunggu_pembayaran' => 0, 'dibayar' => 1, 'selesai' => 0, 'batal' => 0], $r['jumlah_per_status']);
        $this->assertSame('400000.00', $r['total_transaksi']);
        $this->assertSame(1, $r['jumlah_transaksi']);
        $this->assertSame('60000.00', $r['pendapatan_komisi']);
        $this->assertSame(1, $r['tutor_menunggu_verifikasi']);
        $this->assertSame(1, $r['keluhan_terbuka']);
        $this->assertSame([$tutor->id_tutor => 2], $r['tutor_terlaris']->pluck('jumlah_pesanan', 'id_tutor')->all());
        $this->assertCount(30, $r['pesanan_per_hari']);
        $this->assertSame(2, $r['pesanan_per_hari'][3]);
        $this->assertSame(1, $r['pesanan_per_hari'][10]);
        $this->assertSame(0, $r['pesanan_per_hari'][4]);

        $oktober = app(LaporanRingkasan::class)->untuk('2026-10');
        $this->assertSame(1, $oktober['jumlah_pesanan']);
        $this->assertSame(31, count($oktober['pesanan_per_hari']));
    }

    public function test_summary_page_greets_the_admin_and_shows_the_chosen_period(): void
    {
        $this->travelTo(Carbon::parse('2026-11-20 10:00'));
        $this->pesananDibayar();
        $admin = AkunPengguna::factory()->admin()->create(['nama' => 'Dewi Lestari']);

        $this->actingAs($admin)->get(route('admin.beranda'))
            ->assertOk()
            ->assertSeeText('Selamat datang, Admin Dewi Lestari')
            ->assertSeeText('ringkasan November 2026')
            ->assertSeeText('Pesanan per hari')
            ->assertSeeText('Lihat sebagai tabel');

        $this->actingAs($admin)->get(route('admin.beranda', ['periode' => '2026-09']))
            ->assertSeeText('ringkasan September 2026')
            ->assertSeeText('Tidak ada pesanan pada September 2026.');
    }

    public function test_invalid_period_falls_back_to_the_current_month(): void
    {
        $this->travelTo(Carbon::parse('2026-11-20 10:00'));

        $this->actingAs(AkunPengguna::factory()->admin()->create())->get(route('admin.beranda', ['periode' => '2026-13']))
            ->assertOk()
            ->assertSeeText('ringkasan November 2026');
    }
}
