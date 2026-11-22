<?php

namespace Tests\Feature\Auth;

use App\Enums\JenisNotifikasi;
use App\Http\Controllers\Auth\KataSandiController;
use App\Models\AkunPengguna;
use App\Models\Tutor;
use App\Notifications\Pemberitahuan;
use App\Notifications\TautanResetKataSandi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class KataSandiTest extends TestCase
{
    use RefreshDatabase;

    private AkunPengguna $siti;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->siti = AkunPengguna::factory()->ortu()->create(['kontak' => 'siti@contoh.com']);
    }

    private function mintaTautan(string $email = 'Siti@Contoh.com'): ?string
    {
        $this->post(route('password.email'), ['kontak' => $email]);

        $url = null;
        Notification::assertSentTo($this->siti, TautanResetKataSandi::class, function (TautanResetKataSandi $n) use (&$url): bool {
            $url = $n->url;

            return true;
        });

        return $url;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function tokenDari(string $url): array
    {
        $bagian = parse_url($url);
        parse_str($bagian['query'] ?? '', $query);

        return [basename($bagian['path']), $query['kontak']];
    }

    public function test_sign_in_page_links_to_forgot_password(): void
    {
        $this->get(route('login'))->assertSee(route('password.request'), false)->assertSeeText('Lupa kata sandi?');
        $this->get(route('password.request'))->assertOk()->assertSee(route('tutor.aktivasi'), false);
    }

    public function test_registered_and_unknown_emails_get_the_same_answer(): void
    {
        config(['edulink.mode_demo' => false]);
        Tutor::factory()->internal()->draf()->create(['kontak' => 'lestari@contoh.com']);

        $jawaban = [];
        foreach (['siti@contoh.com', 'tidak.ada@contoh.com', 'lestari@contoh.com'] as $email) {
            $response = $this->post(route('password.email'), ['kontak' => $email])->assertRedirect(route('password.request'));
            $jawaban[] = [$response->getStatusCode(), session('status'), session()->has('tautan_demo')];
        }

        $this->assertSame([[302, KataSandiController::PESAN_TERKIRIM, false]], array_values(array_unique($jawaban, SORT_REGULAR)));
        Notification::assertSentTimes(TautanResetKataSandi::class, 1);
        $this->assertSame(1, DB::table('password_reset_tokens')->where('kontak', 'siti@contoh.com')->count());
    }

    public function test_mode_demo_shows_the_link_on_screen_only_with_the_flag_on(): void
    {
        config(['edulink.mode_demo' => true, 'edulink.tautan_di_layar' => true]);
        $this->post(route('password.email'), ['kontak' => 'siti@contoh.com'])->assertSessionHas('tautan_demo');
        $this->post(route('password.email'), ['kontak' => 'tidak.ada@contoh.com'])->assertSessionMissing('tautan_demo');

        // Past the broker's one-request-a-minute limit, so a link is really sent again.
        $this->travel(2)->minutes();
        config(['edulink.tautan_di_layar' => false]);
        $this->post(route('password.email'), ['kontak' => 'siti@contoh.com'])->assertSessionMissing('tautan_demo');
        $this->get(route('password.request'))->assertDontSee('lupa-kata-sandi/', false);

        // The link still goes out by email (the mail log in the demo).
        Notification::assertSentToTimes($this->siti, TautanResetKataSandi::class, 2);
    }

    public function test_an_admin_reset_link_is_never_shown_on_screen(): void
    {
        config(['edulink.mode_demo' => true, 'edulink.tautan_di_layar' => true]);
        $admin = AkunPengguna::factory()->admin()->create(['kontak' => 'admin@contoh.com']);

        $this->post(route('password.email'), ['kontak' => 'admin@contoh.com'])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status', KataSandiController::PESAN_TERKIRIM)
            ->assertSessionMissing('tautan_demo');

        // Sent by email like any other account, just not shown.
        Notification::assertSentTo($admin, TautanResetKataSandi::class);
    }

    public function test_the_link_sets_a_new_password_once_and_ends_other_sessions(): void
    {
        [$token, $kontak] = $this->tokenDari($this->mintaTautan());
        $hashLama = $this->siti->getAuthPassword();

        $this->get(route('password.reset', ['token' => $token, 'kontak' => $kontak]))->assertOk()->assertSee('value="siti@contoh.com"', false);

        $this->post(route('password.update'), ['token' => $token, 'kontak' => $kontak, 'password' => 'baruSekali1', 'password_confirmation' => 'baruSekali1'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('baruSekali1', $this->siti->refresh()->kredensial));
        Notification::assertSentTo($this->siti, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::KataSandiDiubah);

        // A session still holding the old password hash is signed out on its next request.
        $this->actingAs($this->siti)->withSession(['password_hash_web' => $hashLama])->get(route('beranda'))->assertRedirect(route('login'));
        $this->assertGuest();

        // Used once: the same link no longer works.
        $this->post(route('password.update'), ['token' => $token, 'kontak' => $kontak, 'password' => 'lainLagi12', 'password_confirmation' => 'lainLagi12'])
            ->assertSessionHasErrors('kontak');
        $this->assertTrue(Hash::check('baruSekali1', $this->siti->refresh()->kredensial));
    }

    public function test_the_link_expires_after_sixty_minutes(): void
    {
        [$token, $kontak] = $this->tokenDari($this->mintaTautan());

        $this->travel(61)->minutes();

        $this->post(route('password.update'), ['token' => $token, 'kontak' => $kontak, 'password' => 'baruSekali1', 'password_confirmation' => 'baruSekali1'])
            ->assertSessionHasErrors('kontak');
        $this->assertFalse(Hash::check('baruSekali1', $this->siti->refresh()->kredensial));
    }

    public function test_a_token_does_not_work_for_another_email(): void
    {
        AkunPengguna::factory()->ortu()->create(['kontak' => 'budi@contoh.com']);
        [$token] = $this->tokenDari($this->mintaTautan());

        $this->post(route('password.update'), ['token' => $token, 'kontak' => 'budi@contoh.com', 'password' => 'baruSekali1', 'password_confirmation' => 'baruSekali1'])
            ->assertSessionHasErrors('kontak');
    }

    public function test_reset_requests_are_rate_limited_per_email(): void
    {
        foreach (range(1, 3) as $percobaan) {
            $this->post(route('password.email'), ['kontak' => 'siti@contoh.com'])->assertRedirect();
        }

        $this->post(route('password.email'), ['kontak' => 'siti@contoh.com'])->assertTooManyRequests();
    }

    /**
     * @return array<string, mixed>
     */
    private function isianGanti(string $lama = 'password'): array
    {
        return ['kata_sandi_lama' => $lama, 'password' => 'sandiBaru99', 'password_confirmation' => 'sandiBaru99'];
    }

    #[TestWith(['ortu', 'akun.edit'])]
    #[TestWith(['admin', 'admin.akun.edit'])]
    #[TestWith(['tutor', 'tutor.profil.edit'])]
    public function test_every_role_changes_the_password_with_the_old_one(string $peran, string $halaman): void
    {
        $akun = $peran === 'tutor' ? Tutor::factory()->siapDipesan()->denganAkun()->create()->akun : AkunPengguna::factory()->{$peran}()->create();

        $this->actingAs($akun)->get(route($halaman))->assertOk()->assertSee(route('kata-sandi.ganti'), false);

        $this->actingAs($akun)->from(route($halaman))->put(route('kata-sandi.ganti'), $this->isianGanti('salah123'))
            ->assertRedirect(route($halaman))
            ->assertSessionHasErrorsIn('kataSandi', ['kata_sandi_lama' => 'Kata sandi lama belum cocok.']);
        $this->assertTrue(Hash::check('password', $akun->refresh()->kredensial));

        $this->actingAs($akun)->from(route($halaman))->put(route('kata-sandi.ganti'), $this->isianGanti())->assertSessionHas('status');

        $this->assertTrue(Hash::check('sandiBaru99', $akun->refresh()->kredensial));
        $this->assertAuthenticatedAs($akun);
        $this->actingAs($akun)->get(route($halaman))->assertOk();
        Notification::assertSentTo($akun, Pemberitahuan::class, fn (Pemberitahuan $n, array $channels) => $n->jenis === JenisNotifikasi::KataSandiDiubah && in_array('mail', $channels, true));
    }

    public function test_changing_the_password_signs_out_other_sessions(): void
    {
        $hashLama = $this->siti->getAuthPassword();

        $this->actingAs($this->siti)->put(route('kata-sandi.ganti'), $this->isianGanti());

        $this->actingAs($this->siti)->withSession(['password_hash_web' => $hashLama])->get(route('beranda'))->assertRedirect(route('login'));
    }

    public function test_guests_cannot_change_a_password(): void
    {
        $this->put(route('kata-sandi.ganti'), $this->isianGanti())->assertRedirect(route('login'));
    }
}
