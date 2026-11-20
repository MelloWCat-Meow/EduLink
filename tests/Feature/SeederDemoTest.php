<?php

namespace Tests\Feature;

use App\Enums\Peran;
use App\Models\AkunPengguna;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederDemoTest extends TestCase
{
    use RefreshDatabase;

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
}
