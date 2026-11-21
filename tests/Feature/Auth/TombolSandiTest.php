<?php

namespace Tests\Feature\Auth;

use App\Models\AkunPengguna;
use App\Models\Tutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TombolSandiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every password input on the page has exactly one toggle, wired to it,
     * rendered hidden (JavaScript reveals it) and with the right labels.
     *
     * @param  list<string>  $idInput
     */
    private function assertTombolUntuk(TestResponse $response, array $idInput): void
    {
        $response->assertOk();
        $html = $response->getContent();

        $this->assertSame(count($idInput), substr_count($html, 'type="password"'), 'jumlah kolom kata sandi');
        $this->assertSame(count($idInput), substr_count($html, 'data-tampilkan-sandi'), 'satu tombol per kolom');

        foreach ($idInput as $id) {
            $response->assertSee('<button type="button" hidden data-tampilkan-sandi aria-controls="'.$id.'" aria-pressed="false" aria-label="Tampilkan kata sandi"', false);
            $this->assertMatchesRegularExpression('/<div class="kolom-sandi relative[^"]*">\s*<input[^>]*id="'.preg_quote($id, '/').'"[^>]*type="password"/s', $html);
        }
    }

    public function test_sign_in_and_both_sign_up_forms(): void
    {
        $this->assertTombolUntuk($this->get(route('login')), ['isian-password']);
        $this->assertTombolUntuk($this->get(route('register')), ['isian-password', 'isian-password_confirmation']);
        $this->assertTombolUntuk($this->get(route('tutor.register')), ['isian-password', 'isian-password_confirmation']);
    }

    public function test_reset_and_activation_forms(): void
    {
        $this->assertTombolUntuk($this->get(route('password.reset', ['token' => 'abc', 'kontak' => 'siti@contoh.com'])), ['isian-password', 'isian-password_confirmation']);

        $tutor = Tutor::factory()->internal()->draf()->create();
        $url = URL::temporarySignedRoute('tutor.aktivasi.sandi', now()->addHour(), ['tutor' => $tutor->id_tutor]);
        $this->assertTombolUntuk($this->get($url), ['isian-password', 'isian-password_confirmation']);
    }

    public function test_change_password_form_for_every_role(): void
    {
        $id = ['sandi-kata_sandi_lama', 'sandi-password', 'sandi-password_confirmation'];

        $this->assertTombolUntuk($this->actingAs(AkunPengguna::factory()->ortu()->create())->get(route('akun.edit')), $id);
        $this->assertTombolUntuk($this->actingAs(AkunPengguna::factory()->admin()->create())->get(route('admin.akun.edit')), $id);
        $this->assertTombolUntuk($this->actingAs(Tutor::factory()->siapDipesan()->denganAkun()->create()->akun)->get(route('tutor.profil.edit')), $id);
    }

    public function test_the_toggle_stays_with_its_field_when_the_field_shows_an_error(): void
    {
        $response = $this->followingRedirects()->from(route('login'))->post(route('login.store'), ['kontak' => 'siti@contoh.com', 'password' => '']);

        $response->assertSee('aria-invalid="true"', false);
        $this->assertTombolUntuk($response, ['isian-password']);
        $response->assertSeeText('Kata sandi belum diisi.');

        $ortu = AkunPengguna::factory()->ortu()->create();
        $ganti = $this->actingAs($ortu)->followingRedirects()->from(route('akun.edit'))->put(route('kata-sandi.ganti'), ['kata_sandi_lama' => 'salah123', 'password' => 'baruSekali1', 'password_confirmation' => 'baruSekali1']);

        $ganti->assertSee('id="sandi-kata_sandi_lama-galat"', false)->assertSeeText('Kata sandi lama belum cocok.');
        $this->assertTombolUntuk($ganti, ['sandi-kata_sandi_lama', 'sandi-password', 'sandi-password_confirmation']);
    }

    public function test_other_fields_get_no_toggle(): void
    {
        $this->get(route('password.request'))->assertDontSee('data-tampilkan-sandi', false);
    }

    public function test_the_script_and_styles_are_in_the_build_entry(): void
    {
        $this->assertStringContainsString("import { pasangTombolSandi } from './kata-sandi';", file_get_contents(resource_path('js/app.js')));
        $this->assertStringContainsString('::-ms-reveal', file_get_contents(resource_path('css/app.css')));
    }
}
