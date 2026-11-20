<?php

namespace Tests\Feature;

use App\Enums\Peran;
use App\Enums\StatusPesanan;
use App\Enums\StatusTutor;
use App\Enums\StatusVerifikasiTutor;
use App\Models\AkunPengguna;
use App\Models\DokumenVerifikasi;
use App\Models\Pesanan;
use App\Models\Tutor;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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
}
