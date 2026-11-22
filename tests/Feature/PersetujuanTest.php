<?php

namespace Tests\Feature;

use App\Mail\TautanAktivasiTutor;
use App\Models\AkunPengguna;
use App\Models\ProfilTutor;
use App\Models\Tutor;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PersetujuanTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_and_terms_pages_explain_the_rules_and_say_they_are_drafts(): void
    {
        $this->get(route('privacy'))->assertOk()
            ->assertSeeText('Undang-Undang Nomor 27 Tahun 2022')
            ->assertSeeText('hanya bisa dibuka oleh tutor itu sendiri dan admin')
            ->assertSeeText('belum ditinjau oleh ahli hukum');

        $this->get(route('syarat'))->assertOk()
            ->assertSeeText('Tutor menjawab (menerima atau menolak) dalam 24 jam.')
            ->assertSeeText('Penjadwalan ulang sesi')
            ->assertSeeText('Pengembalian dana')
            ->assertSeeText('Larangan bertransaksi di luar platform')
            ->assertSeeText('48 jam')
            ->assertSeeText('Dana ditahan sampai sesi dikonfirmasi.')
            ->assertSeeText('Siswa tidak hadir berarti tutor sudah datang')
            ->assertSeeText('sesi tidak sah');

        $this->get(route('landing'))->assertSee(route('syarat'), false)->assertSee(route('privacy'), false);
        $this->actingAs(AkunPengguna::factory()->ortu()->create())->get(route('beranda'))->assertSee(route('syarat'), false);
    }

    /**
     * @return array<string, string>
     */
    private function isianOrtu(): array
    {
        return ['nama' => 'Siti', 'kontak' => 'siti@contoh.com', 'no_hp' => '081377200101', 'password' => 'rahasia123', 'password_confirmation' => 'rahasia123'];
    }

    public function test_parent_sign_up_needs_the_agreement_and_records_when(): void
    {
        $this->get(route('register'))->assertSee('name="setuju"', false)->assertSee('target="_blank"', false);

        $this->post(route('register.store'), $this->isianOrtu())
            ->assertSessionHasErrors(['setuju' => 'Centang persetujuan Syarat dan Ketentuan serta Kebijakan Privasi untuk melanjutkan.']);
        $this->assertSame(0, AkunPengguna::query()->count());

        $this->freezeTime();
        $this->post(route('register.store'), $this->isianOrtu() + ['setuju' => '1'])->assertRedirect();

        $akun = AkunPengguna::query()->sole();
        $this->assertSame(now()->toDateTimeString(), $akun->persetujuan_pada->toDateTimeString());
        $this->assertSame('2026-11', $akun->versi_kebijakan);
    }

    public function test_external_tutor_sign_up_needs_the_agreement(): void
    {
        $isian = $this->isianOrtu();

        $this->post(route('tutor.register.store'), $isian)->assertSessionHasErrors('setuju');
        $this->post(route('tutor.register.store'), $isian + ['setuju' => 'on']);

        $this->assertNotNull(AkunPengguna::query()->sole()->persetujuan_pada);
    }

    public function test_internal_tutor_activation_needs_the_agreement(): void
    {
        Mail::fake();
        Tutor::factory()->internal()->draf()->has(ProfilTutor::factory(), 'profil')->create(['kontak' => 'lestari@contoh.com']);
        $this->post(route('tutor.aktivasi.store'), ['kontak' => 'lestari@contoh.com']);
        $url = null;
        Mail::assertSent(TautanAktivasiTutor::class, function (TautanAktivasiTutor $mail) use (&$url): bool {
            $url = $mail->url;

            return true;
        });

        $this->get($url)->assertSee('name="setuju"', false);
        $this->post($url, ['password' => 'rahasia123', 'password_confirmation' => 'rahasia123'])->assertSessionHasErrors('setuju');
        $this->assertSame(0, AkunPengguna::query()->count());

        $this->post($url, ['password' => 'rahasia123', 'password_confirmation' => 'rahasia123', 'setuju' => '1']);
        $this->assertSame('2026-11', AkunPengguna::query()->sole()->versi_kebijakan);
    }

    public function test_seeded_accounts_have_agreed_at_seeding_time(): void
    {
        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(0, AkunPengguna::query()->whereNull('persetujuan_pada')->count());
        $this->assertSame(0, AkunPengguna::query()->where('versi_kebijakan', '!=', '2026-11')->count());
    }
}
