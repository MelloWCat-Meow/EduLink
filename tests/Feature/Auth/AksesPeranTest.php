<?php

namespace Tests\Feature\Auth;

use App\Models\AkunPengguna;
use App\Models\Siswa;
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

    public function test_guardian_home_greets_by_name_and_lists_their_children_only(): void
    {
        $ortu = AkunPengguna::factory()->ortu()->create(['nama' => 'Siti Aminah']);
        Siswa::factory()->for($ortu, 'wali')->create(['nama' => 'Nadia Putri', 'jenjang' => 'SD']);
        Siswa::factory()->create(['nama' => 'Anak Orang Lain']);

        $response = $this->actingAs($ortu)->get(route('beranda'));

        $response->assertOk();
        $response->assertSeeText('Selamat datang, Siti Aminah');
        $response->assertSeeText('Nadia Putri');
        $response->assertDontSeeText('Anak Orang Lain');
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

    public function test_tutor_sign_up_is_still_a_coming_soon_page(): void
    {
        $response = $this->get(route('tutor.register'));

        $response->assertOk();
        $response->assertSeeText('Pendaftaran tutor segera hadir.');
    }
}
