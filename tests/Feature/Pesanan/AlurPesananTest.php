<?php

namespace Tests\Feature\Pesanan;

use App\Enums\Kehadiran;
use App\Enums\StatusJadwal;
use App\Enums\StatusPesanan;
use App\Exceptions\JadwalTerisi;
use App\Exceptions\TransisiStatusTidakSah;
use App\Models\AkunPengguna;
use App\Models\Pengaturan;
use App\Services\AlurPesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class AlurPesananTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    private function alur(): AlurPesanan
    {
        return app(AlurPesanan::class);
    }

    public function test_new_order_holds_the_slot_for_24_hours_and_fixes_the_total(): void
    {
        $this->freezeTime();
        $tutor = $this->tutorSiapDipesan(['tarif' => '75000.00']);

        $pesanan = $this->pesananBaru(tutor: $tutor, ubah: ['durasi' => 90, 'jumlah_pertemuan' => 4]);

        $this->assertMatchesRegularExpression('/^ORD-\d{4}-\d{6}$/', $pesanan->id_pesanan);
        $this->assertSame(StatusPesanan::MenungguKonfirmasiTutor, $pesanan->status);
        $this->assertSame('450000.00', $pesanan->total_biaya);
        $this->assertSame(now()->addHours(24)->toDateTimeString(), $pesanan->ditahan_hingga->toDateTimeString());
        $this->assertSame(StatusJadwal::Ditahan, $tutor->jadwal->first()->refresh()->status);
    }

    public function test_total_keeps_the_booking_rate_after_the_tutor_changes_it(): void
    {
        $tutor = $this->tutorSiapDipesan(['tarif' => '75000.00']);
        $pesanan = $this->pesananBaru(tutor: $tutor);

        $tutor->profil->update(['tarif' => '150000.00']);

        $this->assertSame('300000.00', $pesanan->refresh()->total_biaya);
    }

    public function test_tutor_accepting_moves_to_awaiting_payment_with_a_new_deadline(): void
    {
        $pesanan = $this->pesananBaru();
        $this->travel(5)->hours();
        $this->freezeTime();

        $this->alur()->terima($pesanan);

        $this->assertSame(StatusPesanan::MenungguPembayaran, $pesanan->status);
        $this->assertSame(now()->addHours(24)->toDateTimeString(), $pesanan->ditahan_hingga->toDateTimeString());
    }

    public function test_tutor_rejecting_frees_the_slot(): void
    {
        $pesanan = $this->pesananBaru();

        $this->alur()->tolak($pesanan);

        $this->assertSame(StatusPesanan::Ditolak, $pesanan->status);
        $this->assertSame(StatusJadwal::Tersedia, $pesanan->jadwal()->first()->status);
        $this->assertNull($pesanan->ditahan_hingga);
    }

    public function test_an_order_cannot_be_marked_paid_before_the_tutor_accepts(): void
    {
        $pesanan = $this->pesananBaru();

        $this->expectException(TransisiStatusTidakSah::class);

        try {
            $this->alur()->tandaiDibayar($pesanan);
        } finally {
            $this->assertSame(StatusPesanan::MenungguKonfirmasiTutor, $pesanan->refresh()->status);
            $this->assertSame(0, $pesanan->sesi()->count());
        }
    }

    public function test_a_rejected_order_cannot_be_accepted_afterwards(): void
    {
        $pesanan = $this->pesananBaru();
        $this->alur()->tolak($pesanan);

        $this->expectException(TransisiStatusTidakSah::class);

        $this->alur()->terima($pesanan);
    }

    public function test_parent_cannot_cancel_a_paid_order(): void
    {
        $pesanan = $this->pesananDibayar();
        $wali = AkunPengguna::query()->findOrFail($pesanan->siswa()->value('id_akun'));

        $this->expectException(TransisiStatusTidakSah::class);

        try {
            $this->alur()->batalkanOlehOrtu($pesanan, $wali);
        } finally {
            $this->assertSame(StatusPesanan::Dibayar, $pesanan->refresh()->status);
        }
    }

    public function test_admin_cancellation_records_the_reason_and_frees_the_slot(): void
    {
        $pesanan = $this->pesananDibayar();
        $admin = AkunPengguna::factory()->admin()->create();

        $this->alur()->batalkanOlehAdmin($pesanan, 'Tutor sakit panjang.', $admin);

        $this->assertSame(StatusPesanan::Batal, $pesanan->status);
        $this->assertSame(StatusJadwal::Tersedia, $pesanan->jadwal()->first()->status);
        $this->assertDatabaseHas('riwayat_status_pesanan', [
            'id_pesanan' => $pesanan->id_pesanan,
            'status' => 'batal',
            'catatan' => 'Dibatalkan admin: Tutor sakit panjang.',
            'id_akun' => $admin->id_akun,
        ]);
    }

    public function test_expired_hold_is_cancelled_by_the_scheduled_command_and_the_slot_is_freed(): void
    {
        $pesanan = $this->pesananMenungguPembayaran();
        $this->travel(25)->hours();

        $this->artisan('edulink:lepas-penahanan')->assertSuccessful();

        $this->assertSame(StatusPesanan::Batal, $pesanan->refresh()->status);
        $this->assertSame(StatusJadwal::Tersedia, $pesanan->jadwal()->first()->status);
    }

    public function test_hold_that_has_not_expired_is_kept(): void
    {
        $pesanan = $this->pesananBaru();
        $this->travel(23)->hours();

        $this->artisan('edulink:lepas-penahanan')->assertSuccessful();

        $this->assertSame(StatusPesanan::MenungguKonfirmasiTutor, $pesanan->refresh()->status);
    }

    public function test_accepting_after_the_deadline_cancels_the_order_instead(): void
    {
        $pesanan = $this->pesananBaru();
        $this->travel(25)->hours();

        try {
            $this->alur()->terima($pesanan);
            $this->fail('Accepting an expired order should be refused.');
        } catch (TransisiStatusTidakSah) {
            $this->assertSame(StatusPesanan::Batal, $pesanan->refresh()->status);
            $this->assertSame(StatusJadwal::Tersedia, $pesanan->jadwal()->first()->status);
        }
    }

    public function test_a_held_slot_cannot_be_booked_by_another_parent(): void
    {
        $tutor = $this->tutorSiapDipesan();
        $this->pesananBaru(tutor: $tutor);

        $this->expectException(JadwalTerisi::class);

        $this->pesananBaru(tutor: $tutor);
    }

    public function test_slot_freed_by_an_expired_hold_can_be_booked_at_once(): void
    {
        $tutor = $this->tutorSiapDipesan();
        $pertama = $this->pesananBaru(tutor: $tutor);
        $this->travel(25)->hours();

        $kedua = $this->pesananBaru(tutor: $tutor);

        $this->assertSame(StatusPesanan::Batal, $pertama->refresh()->status);
        $this->assertSame(StatusPesanan::MenungguKonfirmasiTutor, $kedua->status);
    }

    public function test_paid_order_fills_the_slot_and_creates_weekly_sessions_with_meeting_links(): void
    {
        $pesanan = $this->pesananDibayar(ubah: ['jumlah_pertemuan' => 3]);
        $jadwal = $pesanan->jadwal()->first();

        $sesi = $pesanan->sesi()->orderBy('waktu')->get();

        $this->assertSame(StatusJadwal::Terisi, $jadwal->status);
        $this->assertCount(3, $sesi);
        $this->assertSame($pesanan->tanggal_mulai->toDateString().' '.$jadwal->jam, $sesi[0]->waktu->toDateTimeString());
        $this->assertSame(7, (int) $sesi[0]->waktu->diffInDays($sesi[1]->waktu));
        $this->assertStringStartsWith('https://meet.edulink.test/', $sesi[0]->tautan_meeting);
        $this->assertNull($pesanan->ditahan_hingga);
    }

    public function test_order_finishes_only_when_every_session_is_logged_and_then_frees_the_slot(): void
    {
        $pesanan = $this->pesananDibayar(ubah: ['jumlah_pertemuan' => 2]);
        [$pertama, $kedua] = $pesanan->sesi()->orderBy('waktu')->get();
        $this->travelTo($kedua->waktu->copy()->addMinutes(5));

        $this->alur()->catatKehadiran($pertama, Kehadiran::Hadir);
        $this->assertSame(StatusPesanan::Dibayar, $pesanan->refresh()->status);

        $this->alur()->catatKehadiran($kedua, Kehadiran::TidakHadir);
        $this->assertSame(StatusPesanan::Selesai, $pesanan->refresh()->status);
        $this->assertSame(StatusJadwal::Tersedia, $pesanan->jadwal()->first()->status);
    }

    public function test_commission_uses_the_percentage_in_force_when_each_order_is_paid(): void
    {
        Pengaturan::simpan(Pengaturan::KOMISI_PERSEN, '10');
        $lama = $this->pesananDibayar();

        Pengaturan::simpan(Pengaturan::KOMISI_PERSEN, '20');
        $baru = $this->pesananDibayar();

        $this->assertSame(['10.00', '30000.00'], [$lama->komisi()->value('persentase'), $lama->komisi()->value('nominal')]);
        $this->assertSame(['20.00', '60000.00'], [$baru->komisi()->value('persentase'), $baru->komisi()->value('nominal')]);
    }

    public function test_commission_defaults_to_15_percent(): void
    {
        $pesanan = $this->pesananDibayar();

        $this->assertSame('15.00', $pesanan->komisi()->value('persentase'));
        $this->assertSame('45000.00', $pesanan->komisi()->value('nominal'));
    }
}
