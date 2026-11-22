<?php

namespace Tests\Feature;

use App\Models\AkunPengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class PesanValidasiTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    /**
     * Fails when a message is an untranslated key or reads as Laravel's English default.
     *
     * @param  list<string>  $pesan
     */
    private function assertBahasaIndonesia(array $pesan, string $konteks): void
    {
        $this->assertNotEmpty($pesan, $konteks.': tidak ada pesan galat');

        foreach ($pesan as $satu) {
            $this->assertStringNotContainsString('validation.', $satu, $konteks);
            $this->assertDoesNotMatchRegularExpression('/\b(The|field|must|is required|is invalid|selected)\b/', $satu, $konteks.': '.$satu);
        }
    }

    /**
     * @return list<string>
     */
    private function pesanDariSesi(): array
    {
        $galat = session('errors');

        return $galat instanceof ViewErrorBag
            ? collect($galat->getBags())->flatMap->all()->all()
            : [];
    }

    #[TestWith(['tamu', 'post', 'register.store'])]
    #[TestWith(['tamu', 'post', 'login.store'])]
    #[TestWith(['tamu', 'post', 'password.email'])]
    #[TestWith(['tamu', 'post', 'tutor.register.store'])]
    #[TestWith(['ortu', 'post', 'anak.store'])]
    #[TestWith(['ortu', 'put', 'akun.update'])]
    #[TestWith(['admin', 'put', 'admin.komisi.update'])]
    #[TestWith(['admin', 'put', 'admin.akun.update'])]
    public function test_an_empty_form_answers_in_indonesian(string $peran, string $metode, string $route): void
    {
        if ($peran !== 'tamu') {
            $this->actingAs(AkunPengguna::factory()->{$peran}()->create());
        }

        $this->{$metode}(route($route), [])->assertSessionHasErrors();

        $this->assertBahasaIndonesia($this->pesanDariSesi(), $route);
    }

    public function test_a_rule_without_its_own_message_uses_the_indonesian_file(): void
    {
        $admin = AkunPengguna::factory()->admin()->create();
        $ulasan = $this->pesananSelesai()->ulasan()->create(['nilai' => 1, 'komentar' => 'Hubungi saya di 0812.']);

        // ModerasiUlasanRequest has no message of its own for "boolean".
        $this->actingAs($admin)->put(route('admin.ulasan.update', $ulasan), ['ditampilkan' => 'mungkin', 'alasan' => 'Memuat nomor telepon.'])
            ->assertSessionHasErrors(['ditampilkan' => 'Status tampil harus bernilai ya atau tidak.']);
    }

    public function test_the_validation_file_covers_rules_with_readable_field_names(): void
    {
        $this->assertSame('id', app()->getLocale());

        $galat = Validator::make(
            ['kontak' => 'bukan-email', 'jumlah_pertemuan' => 'dua', 'tanggal_mulai' => 'besok', 'nama' => str_repeat('a', 300)],
            ['kontak' => 'email', 'jumlah_pertemuan' => 'integer', 'tanggal_mulai' => 'date', 'nama' => 'max:100', 'no_hp' => 'required'],
        )->errors();

        $this->assertSame('Email harus berupa alamat email yang valid.', $galat->first('kontak'));
        $this->assertSame('Jumlah pertemuan harus berupa bilangan bulat.', $galat->first('jumlah_pertemuan'));
        $this->assertSame('Tanggal mulai harus berupa tanggal yang valid.', $galat->first('tanggal_mulai'));
        $this->assertSame('Nama paling banyak 100 karakter.', $galat->first('nama'));
        $this->assertSame('Nomor HP wajib diisi.', $galat->first('no_hp'));
    }
}
