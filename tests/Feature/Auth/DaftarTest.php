<?php

namespace Tests\Feature\Auth;

use App\Enums\Peran;
use App\Models\AkunPengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DaftarTest extends TestCase
{
    use RefreshDatabase;

    public function test_sign_up_page_shows_the_guardian_sheet(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSeeText('Lembar pendaftaran');
        $response->assertSee('name="_token"', false);
    }

    public function test_guardian_signs_up_as_ortu_with_a_prefixed_id_and_is_signed_in(): void
    {
        $response = $this->post(route('register.store'), [
            'nama' => 'Rina Marlina',
            'kontak' => 'Rina@Contoh.com',
            'no_hp' => '0812 3456 7890',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123', 'setuju' => '1',
        ]);

        $akun = AkunPengguna::query()->sole();
        $response->assertRedirect(route('beranda'));
        $this->assertAuthenticatedAs($akun);
        $this->assertSame('AK0001', $akun->id_akun);
        $this->assertSame(Peran::Ortu, $akun->peran);
        $this->assertSame('rina@contoh.com', $akun->kontak);
        $this->assertTrue(Hash::check('rahasia123', $akun->kredensial));
        $this->assertSame('081234567890', $akun->no_hp);
    }

    public function test_sign_up_requires_an_indonesian_phone_number(): void
    {
        $isian = ['nama' => 'Rina Marlina', 'kontak' => 'rina@contoh.com', 'password' => 'rahasia123', 'password_confirmation' => 'rahasia123', 'setuju' => '1'];

        $this->post(route('register.store'), $isian)->assertSessionHasErrors(['no_hp' => 'Nomor HP belum diisi.']);
        $this->post(route('register.store'), $isian + ['no_hp' => '12345'])->assertSessionHasErrors(['no_hp' => 'Tulis nomor HP Indonesia, misalnya 0812 3456 7890.']);

        $this->assertGuest();
        $this->assertDatabaseCount('akun_pengguna', 0);
        $this->get(route('register'))->assertSee('name="no_hp"', false);
    }

    public function test_new_account_id_continues_after_the_highest_existing_id(): void
    {
        AkunPengguna::factory()->admin()->create();
        AkunPengguna::factory()->create();

        $this->post(route('register.store'), [
            'nama' => 'Rina Marlina',
            'kontak' => 'rina@contoh.com',
            'no_hp' => '0812 3456 7890',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123', 'setuju' => '1',
        ]);

        $this->assertDatabaseHas('akun_pengguna', ['kontak' => 'rina@contoh.com', 'id_akun' => 'AK0003']);
    }

    public function test_sign_up_cannot_create_an_admin_account(): void
    {
        $this->post(route('register.store'), [
            'nama' => 'Coba Admin',
            'kontak' => 'coba@contoh.com',
            'no_hp' => '0812 3456 7890',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123', 'setuju' => '1',
            'peran' => 'admin',
        ]);

        $this->assertDatabaseHas('akun_pengguna', ['kontak' => 'coba@contoh.com', 'peran' => 'ortu']);
        $this->assertDatabaseMissing('akun_pengguna', ['peran' => 'admin']);
    }

    public function test_sign_up_rejects_a_short_password_with_an_indonesian_message(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), [
            'nama' => 'Rina Marlina',
            'kontak' => 'rina@contoh.com',
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['password' => 'Kata sandi minimal 8 karakter.']);
        $this->assertGuest();
        $this->assertDatabaseCount('akun_pengguna', 0);
    }

    public function test_mismatched_repeat_password_is_reported_on_the_repeat_field(): void
    {
        $response = $this->post(route('register.store'), [
            'nama' => 'Rina Marlina',
            'kontak' => 'rina@contoh.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia124',
        ]);

        $response->assertSessionHasErrors(['password_confirmation' => 'Ulangan kata sandi belum sama dengan kata sandi di atas.']);
        $response->assertSessionDoesntHaveErrors('password');
        $this->assertGuest();
    }

    public function test_sign_up_rejects_an_email_that_is_already_registered(): void
    {
        AkunPengguna::factory()->create(['kontak' => 'rina@contoh.com']);

        $response = $this->post(route('register.store'), [
            'nama' => 'Rina Lain',
            'kontak' => 'RINA@contoh.com',
            'no_hp' => '0812 3456 7890',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123', 'setuju' => '1',
        ]);

        $response->assertSessionHasErrors(['kontak' => 'Email ini sudah terdaftar. Silakan masuk dengan email tersebut.']);
        $this->assertDatabaseCount('akun_pengguna', 1);
    }

    public function test_sign_up_errors_are_linked_to_their_fields(): void
    {
        $response = $this->followingRedirects()->from(route('register'))->post(route('register.store'), []);

        $response->assertSee('aria-invalid="true"', false);
        $response->assertSee('aria-describedby="isian-nama-galat"', false);
        $response->assertSee('id="isian-nama-galat"', false);
        $response->assertSeeText('Nama belum diisi.');
    }
}
