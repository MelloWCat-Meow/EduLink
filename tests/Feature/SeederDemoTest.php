<?php

namespace Tests\Feature;

use App\Enums\Peran;
use App\Enums\StatusJadwal;
use App\Enums\StatusKeluhan;
use App\Enums\StatusKonfirmasiSesi;
use App\Enums\StatusPesanan;
use App\Enums\StatusTutor;
use App\Enums\StatusVerifikasiTutor;
use App\Models\AkunPengguna;
use App\Models\DokumenVerifikasi;
use App\Models\JadwalKetersediaan;
use App\Models\Keluhan;
use App\Models\PengembalianDana;
use App\Models\Pesanan;
use App\Models\SesiPembelajaran;
use App\Models\Tutor;
use App\Services\SaldoTutor;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SeederDemoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The tutor seeder writes placeholder documents to the private disk.
        Storage::fake('local');
    }

    public function test_seeder_creates_the_admin_as_the_first_account(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = AkunPengguna::query()->findOrFail('AK0001');

        $this->assertSame(Peran::Admin, $admin->peran);
        $this->assertSame('admin@edulink.test', $admin->kontak);
    }

    public function test_seeded_guardians_own_their_children_through_id_akun(): void
    {
        $this->seed(DatabaseSeeder::class);

        $siti = AkunPengguna::query()->where('kontak', 'siti.aminah@edulink.test')->sole();
        $budi = AkunPengguna::query()->where('kontak', 'budi.santoso@edulink.test')->sole();

        $this->assertSame(Peran::Ortu, $siti->peran);
        $this->assertSame(['S0001' => 'SD', 'S0002' => 'SMP'], $siti->siswa()->orderBy('id_siswa')->pluck('jenjang', 'id_siswa')->all());
        $this->assertSame(['S0003' => 'SMA'], $budi->siswa()->pluck('jenjang', 'id_siswa')->all());
        $this->assertSame($siti->id_akun, $siti->siswa()->firstOrFail()->wali->id_akun);
    }

    public function test_seeded_accounts_can_sign_in_with_the_demo_passwords(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->post(route('login.store'), ['kontak' => 'siti.aminah@edulink.test', 'password' => 'orangtua123']);

        $response->assertRedirect(route('beranda'));
        $this->assertAuthenticated();
    }

    public function test_demo_data_covers_every_order_status_and_the_verification_queue(): void
    {
        $this->seed(DatabaseSeeder::class);

        $statuses = Pesanan::query()->distinct()->pluck('status')->map->value->sort()->values()->all();
        $expected = collect(StatusPesanan::cases())->map->value->sort()->values()->all();

        $this->assertSame($expected, $statuses);
        $this->assertSame(8, Tutor::query()->tampil()->count());
        $this->assertSame(2, Tutor::query()->menungguVerifikasi()->count());
        $this->assertTrue(Pesanan::query()->where('status', StatusPesanan::Selesai)->whereDoesntHave('ulasan')->exists());
        $this->assertTrue(Pesanan::query()->where('status', StatusPesanan::Selesai)->whereHas('ulasan')->exists());
    }

    public function test_demo_tutors_have_orders_in_every_status_sessions_to_log_and_payouts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $semuaStatus = collect(StatusPesanan::cases())->map->value->sort()->values()->all();

        foreach (['rina.marlina@edulink.test', 'andi.pratama@edulink.test', 'dewi.anggraini@edulink.test'] as $kontak) {
            $tutor = Tutor::query()->where('kontak', $kontak)->sole();
            $status = $tutor->pesanan()->distinct()->pluck('status')->map->value->sort()->values()->all();

            $this->assertSame($semuaStatus, $status, $kontak);
            $this->assertTrue($tutor->punyaRekening(), $kontak);
            $this->assertTrue($tutor->pencairan()->exists(), $kontak);
            $this->assertNotNull($tutor->no_hp);
        }

        $rina = Tutor::query()->where('kontak', 'rina.marlina@edulink.test')->sole();
        $this->assertTrue(SesiPembelajaran::query()->whereIn('id_pesanan', $rina->pesanan()->select('id_pesanan'))->whereNull('kehadiran')->where('waktu', '<', now())->exists());

        $dewi = Tutor::query()->where('kontak', 'dewi.anggraini@edulink.test')->sole();
        $this->assertTrue(SesiPembelajaran::query()->whereIn('id_pesanan', $dewi->pesanan()->select('id_pesanan'))->whereBetween('waktu', [now()->addDay()->startOfDay(), now()->addDay()->endOfDay()])->exists());

        // The README demo books Andi's Thursday slot, so it must start free.
        $andi = Tutor::query()->where('kontak', 'andi.pratama@edulink.test')->sole();
        $this->assertSame('tersedia', $andi->jadwal()->where('hari', 'kamis')->where('jam', '19:00:00')->sole()->status->value);

        $this->assertSame(0, AkunPengguna::query()->where('peran', Peran::Ortu)->whereNull('no_hp')->count());
        $this->assertSame(0, Tutor::query()->whereNull('no_hp')->count());
    }

    public function test_sign_up_demos_cover_every_application_state(): void
    {
        $this->seed(DatabaseSeeder::class);

        $jumlah = Tutor::query()->get()->countBy(fn (Tutor $tutor): string => $tutor->status_verifikasi->value)->sortKeys()->all();
        $this->assertSame(['draf' => 3, 'menunggu' => 2, 'perlu_perbaikan' => 1, 'terverifikasi' => 8], $jumlah);

        $belumAktif = Tutor::query()->where('status', StatusTutor::Internal)->whereNull('id_akun')->where('status_verifikasi', StatusVerifikasiTutor::Draf)->pluck('kontak')->all();
        $this->assertSame(['lestari.putri@edulink.test', 'rahmat.saputra@edulink.test'], $belumAktif);

        $perbaikan = Tutor::query()->where('status_verifikasi', StatusVerifikasiTutor::PerluPerbaikan)->sole();
        $this->assertNotEmpty($perbaikan->catatan_verifikasi);
        $this->assertArrayHasKey('dokumen', $perbaikan->bagian_perbaikan);
        $this->assertSame(Peran::Tutor, $perbaikan->akun->peran);

        // Every seeded document points at a real file on the private disk.
        foreach (DokumenVerifikasi::query()->pluck('berkas') as $berkas) {
            Storage::disk('local')->assertExists($berkas);
        }
    }

    /**
     * The demo uses slots on "yesterday" and "tomorrow", so the seeder must work
     * whatever day it runs. Every confirmation state is there, and the demo
     * tutors have money both available and held.
     */
    #[TestWith(['2026-11-23 08:00'])]
    #[TestWith(['2026-11-24 13:00'])]
    #[TestWith(['2026-11-25 19:00'])]
    #[TestWith(['2026-11-26 07:30'])]
    #[TestWith(['2026-11-27 21:00'])]
    #[TestWith(['2026-11-28 10:00'])]
    #[TestWith(['2026-11-29 16:00'])]
    public function test_demo_data_covers_every_session_confirmation_state_on_any_day(string $waktu): void
    {
        $this->travelTo(Carbon::parse($waktu));
        $this->seed(DatabaseSeeder::class);

        $status = SesiPembelajaran::query()->whereNotNull('status_konfirmasi')->distinct()->pluck('status_konfirmasi')->map->value->sort()->values()->all();
        $this->assertSame(collect(StatusKonfirmasiSesi::cases())->map->value->sort()->values()->all(), $status);

        $menunggu = SesiPembelajaran::query()->where('status_konfirmasi', StatusKonfirmasiSesi::MenungguKonfirmasi)->get();
        $this->assertTrue($menunggu->contains(fn (SesiPembelajaran $sesi): bool => $sesi->batasKonfirmasi()->isFuture()), 'ada sesi menunggu dengan sisa waktu');
        $this->assertTrue(Keluhan::query()->whereNotNull('id_sesi')->where('status', StatusKeluhan::Baru)->exists(), 'sanggahan menunggu admin');
        $this->assertTrue(PengembalianDana::query()->whereHas('sesi', fn ($sesi) => $sesi->where('status_konfirmasi', StatusKonfirmasiSesi::TidakSah))->exists());

        foreach (['rina.marlina@edulink.test', 'andi.pratama@edulink.test', 'dewi.anggraini@edulink.test'] as $kontak) {
            $saldo = app(SaldoTutor::class)->satu(Tutor::query()->where('kontak', $kontak)->value('id_tutor'));
            $this->assertSame(1, bccomp($saldo['saldo'], '0', 2), $kontak.' saldo');
            $this->assertSame(1, bccomp($saldo['ditahan'], '0', 2), $kontak.' ditahan');
        }

        // The pending payout of the README demo fits Rina's available balance.
        $rina = Tutor::query()->where('kontak', 'rina.marlina@edulink.test')->sole();
        $menungguCair = $rina->pencairan()->where('status', 'menunggu')->sole();
        $this->assertTrue(bccomp((string) $menungguCair->nominal, app(SaldoTutor::class)->satu($rina->id_tutor)['saldo'], 2) <= 0);
    }

    /**
     * Dewi's "tomorrow" slot shares a weekday with one of her fixed slots on
     * some days (on a Sunday it is Monday). The seeder must still pick free
     * slots only, whatever day it runs, and never put two active orders on one slot.
     */
    #[TestWith(['2026-11-23 08:00'])]
    #[TestWith(['2026-11-24 13:00'])]
    #[TestWith(['2026-11-25 19:00'])]
    #[TestWith(['2026-11-26 07:30'])]
    #[TestWith(['2026-11-27 21:00'])]
    #[TestWith(['2026-11-28 10:00'])]
    #[TestWith(['2026-11-29 16:00'])]
    public function test_the_seeder_runs_on_any_day_and_keeps_slots_consistent(string $waktu): void
    {
        $this->travelTo(Carbon::parse($waktu));
        $this->seed(DatabaseSeeder::class);

        $statusSlot = [
            StatusPesanan::MenungguKonfirmasiTutor->value => StatusJadwal::Ditahan,
            StatusPesanan::MenungguPembayaran->value => StatusJadwal::Ditahan,
            StatusPesanan::Dibayar->value => StatusJadwal::Terisi,
        ];
        $aktif = Pesanan::query()->whereIn('status', array_keys($statusSlot))->get();

        $this->assertSame($aktif->count(), $aktif->unique('id_jadwal')->count(), 'satu slot dipakai lebih dari satu pesanan aktif');

        foreach (JadwalKetersediaan::query()->where('status', '!=', StatusJadwal::Nonaktif)->get() as $slot) {
            $pesanan = $aktif->firstWhere('id_jadwal', $slot->id_jadwal);
            $this->assertSame($pesanan ? $statusSlot[$pesanan->status->value] : StatusJadwal::Tersedia, $slot->status, 'slot '.$slot->id_jadwal);
        }
    }
}
