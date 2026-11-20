<?php

namespace Tests\Feature\Auth;

use App\Http\Requests\Auth\MasukRequest;
use App\Models\AkunPengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasukTest extends TestCase
{
    use RefreshDatabase;

    public function test_sign_in_page_shows_the_sheet_with_csrf_token(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSeeText('Lembar masuk');
        $response->assertSee('name="_token"', false);
    }

    public function test_guardian_signs_in_and_lands_on_beranda(): void
    {
        $ortu = AkunPengguna::factory()->ortu()->create(['kontak' => 'siti@contoh.com']);

        $response = $this->post(route('login.store'), ['kontak' => 'siti@contoh.com', 'password' => 'password']);

        $response->assertRedirect(route('beranda'));
        $this->assertAuthenticatedAs($ortu);
    }

    public function test_admin_signs_in_and_lands_on_admin_page(): void
    {
        $admin = AkunPengguna::factory()->admin()->create(['kontak' => 'admin@contoh.com']);

        $response = $this->post(route('login.store'), ['kontak' => 'admin@contoh.com', 'password' => 'password']);

        $response->assertRedirect(route('admin.beranda'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_wrong_password_keeps_the_visitor_signed_out_with_a_message(): void
    {
        AkunPengguna::factory()->create(['kontak' => 'siti@contoh.com']);

        $response = $this->from(route('login'))->post(route('login.store'), ['kontak' => 'siti@contoh.com', 'password' => 'salah-sandi']);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['kontak' => 'Email atau kata sandi belum cocok. Periksa lagi, lalu coba masuk.']);
        $this->assertGuest();
    }

    public function test_sign_in_is_locked_after_too_many_failed_attempts(): void
    {
        AkunPengguna::factory()->create(['kontak' => 'siti@contoh.com']);

        for ($attempt = 0; $attempt < MasukRequest::MAX_ATTEMPTS; $attempt++) {
            $this->post(route('login.store'), ['kontak' => 'siti@contoh.com', 'password' => 'salah-sandi']);
        }

        $response = $this->post(route('login.store'), ['kontak' => 'siti@contoh.com', 'password' => 'password']);

        $response->assertSessionHasErrors('kontak');
        $this->assertStringStartsWith('Terlalu banyak percobaan masuk.', session('errors')->first('kontak'));
        $this->assertGuest();
    }

    public function test_the_reserved_student_role_cannot_sign_in_yet(): void
    {
        AkunPengguna::factory()->create(['kontak' => 'siswa@contoh.com', 'peran' => 'siswa']);

        $response = $this->post(route('login.store'), ['kontak' => 'siswa@contoh.com', 'password' => 'password']);

        $response->assertSessionHasErrors(['kontak' => 'Akun ini belum bisa masuk lewat halaman ini.']);
        $this->assertGuest();
    }

    public function test_signing_out_ends_the_session(): void
    {
        $ortu = AkunPengguna::factory()->create();

        $response = $this->actingAs($ortu)->post(route('logout'));

        $response->assertRedirect(route('landing'));
        $this->assertGuest();
    }

    public function test_signing_out_requires_post(): void
    {
        $ortu = AkunPengguna::factory()->create();

        $response = $this->actingAs($ortu)->get('/keluar');

        $response->assertMethodNotAllowed();
        $this->assertAuthenticatedAs($ortu);
    }
}
