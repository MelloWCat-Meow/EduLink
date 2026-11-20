<?php

namespace Tests\Feature\Auth;

use App\Models\AkunPengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class AksesPeranTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith(['beranda'])]
    #[TestWith(['admin.beranda'])]
    public function test_guests_are_sent_to_the_sign_in_page(string $routeName): void
    {
        $response = $this->get(route($routeName));

        $response->assertRedirect(route('login'));
    }

    public function test_guardian_is_refused_on_the_admin_page(): void
    {
        $ortu = AkunPengguna::factory()->ortu()->create();

        $response = $this->actingAs($ortu)->get(route('admin.beranda'));

        $response->assertForbidden();
        $response->assertSeeText('Halaman ini bukan untuk akun Anda.');
    }

    public function test_admin_is_refused_on_the_guardian_home(): void
    {
        $admin = AkunPengguna::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('beranda'));

        $response->assertForbidden();
    }

    public function test_guardian_home_greets_by_name(): void
    {
        $ortu = AkunPengguna::factory()->ortu()->create(['nama' => 'Siti Aminah']);

        $response = $this->actingAs($ortu)->get(route('beranda'));

        $response->assertOk();
        $response->assertSeeText('Selamat datang, Siti Aminah');
    }

    public function test_admin_page_greets_the_admin_by_name(): void
    {
        $admin = AkunPengguna::factory()->admin()->create(['nama' => 'Dewi Lestari']);

        $response = $this->actingAs($admin)->get(route('admin.beranda'));

        $response->assertOk();
        $response->assertSeeText('Selamat datang, Admin Dewi Lestari');
    }

    #[TestWith(['login'])]
    #[TestWith(['register'])]
    public function test_signed_in_accounts_skip_the_sign_in_and_sign_up_pages(string $routeName): void
    {
        $admin = AkunPengguna::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route($routeName));

        $response->assertRedirect(route('admin.beranda'));
    }

    public function test_tutor_sign_up_opens_the_application_sheet_with_the_activation_link(): void
    {
        $response = $this->get(route('tutor.register'));

        $response->assertOk();
        $response->assertSeeText('Lembar lamaran tutor');
        $response->assertSee(route('tutor.aktivasi'), false);
    }
}
