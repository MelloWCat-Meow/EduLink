<?php

namespace Tests\Feature;

use App\Console\Commands\SiapkanDemo;
use App\Models\AkunPengguna;
use App\Models\Pesanan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiapkanDemoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Not RefreshDatabase: the command runs migrate:fresh, which cannot run
        // inside its transaction on SQLite. Each test gets a new in-memory
        // database anyway, so nothing needs rolling back.
        $this->artisan('migrate')->run();
        Storage::fake('local');
    }

    public function test_it_refuses_outside_demo_mode_and_keeps_the_data(): void
    {
        config(['edulink.mode_demo' => false]);
        $akun = AkunPengguna::factory()->ortu()->create();

        $this->artisan('edulink:siapkan-demo', ['--force' => true])->assertFailed()->run();

        $this->assertModelExists($akun);
    }

    public function test_declining_the_question_changes_nothing(): void
    {
        $akun = AkunPengguna::factory()->ortu()->create();

        $this->artisan('edulink:siapkan-demo')
            ->expectsConfirmation('Semua data di database ":memory:" akan dihapus dan diganti data demo. Lanjutkan?', 'no')
            ->expectsOutputToContain('Dibatalkan')
            ->assertSuccessful()
            ->run();

        $this->assertModelExists($akun);
        $this->assertSame(0, Pesanan::query()->count());
    }

    public function test_it_rebuilds_the_demo_data_and_lists_accounts_that_sign_in(): void
    {
        $lama = AkunPengguna::factory()->ortu()->create();
        Storage::disk('local')->put('dokumen-tutor/lama/berkas.pdf', 'sisa');

        $perintah = $this->artisan('edulink:siapkan-demo')
            ->expectsConfirmation('Semua data di database ":memory:" akan dihapus dan diganti data demo. Lanjutkan?', 'yes')
            ->expectsOutputToContain('edulink:konfirmasi-otomatis --setelah=0');

        foreach (SiapkanDemo::AKUN as [, $email]) {
            $perintah->expectsOutputToContain($email);
        }

        $perintah->assertSuccessful()->run();

        $this->assertDatabaseMissing('akun_pengguna', ['kontak' => $lama->kontak]);
        $this->assertGreaterThan(0, Pesanan::query()->count());
        Storage::disk('local')->assertMissing('dokumen-tutor/lama/berkas.pdf');

        foreach (SiapkanDemo::AKUN as [, $email, $kataSandi]) {
            $akun = AkunPengguna::query()->where('kontak', $email)->sole();
            $this->assertTrue(Hash::check($kataSandi, $akun->kredensial), $email);
        }
    }
}
