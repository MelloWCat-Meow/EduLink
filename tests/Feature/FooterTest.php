<?php

namespace Tests\Feature;

use App\Models\AkunPengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class FooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_footer_shows_the_configured_address_and_whatsapp(): void
    {
        config(['landing.contact' => ['address' => 'Jl. Contoh Raya No. 1, Kota Jambi (alamat contoh)', 'whatsapp' => '6281200000000']]);

        $this->get(route('landing'))
            ->assertSeeText('Kontak')
            ->assertSeeText('Jl. Contoh Raya No. 1, Kota Jambi (alamat contoh)')
            ->assertSee('href="https://wa.me/6281200000000"', false)
            ->assertDontSee('TODO')
            ->assertDontSeeText('[nomor belum diisi]');
    }

    public function test_an_empty_line_is_hidden_and_the_contact_block_goes_when_both_are_empty(): void
    {
        config(['landing.contact' => ['address' => null, 'whatsapp' => '6281200000000']]);
        $this->get(route('landing'))->assertSeeText('Chat admin di WhatsApp')->assertDontSeeText('[Alamat kantor]');

        config(['landing.contact' => ['address' => 'Jl. Contoh Raya No. 1', 'whatsapp' => null]]);
        $this->get(route('landing'))->assertSeeText('Jl. Contoh Raya No. 1')->assertDontSee('wa.me', false);

        config(['landing.contact' => ['address' => null, 'whatsapp' => null]]);
        $this->get(route('landing'))->assertDontSeeText('Kontak')->assertDontSee('wa.me', false);
    }

    #[TestWith(['0812-0000-0000', '6281200000000'])]
    #[TestWith(['+62 812 0000 0000', '6281200000000'])]
    #[TestWith(['6281200000000', '6281200000000'])]
    #[TestWith(['', null])]
    public function test_the_whatsapp_number_is_kept_in_international_digits(string $nilai, ?string $harapan): void
    {
        $sebelum = getenv('EDULINK_WHATSAPP');
        putenv('EDULINK_WHATSAPP='.$nilai);
        $_SERVER['EDULINK_WHATSAPP'] = $_ENV['EDULINK_WHATSAPP'] = $nilai;

        try {
            $this->assertSame($harapan, (require config_path('landing.php'))['contact']['whatsapp']);
        } finally {
            unset($_SERVER['EDULINK_WHATSAPP'], $_ENV['EDULINK_WHATSAPP']);
            putenv($sebelum === false ? 'EDULINK_WHATSAPP' : 'EDULINK_WHATSAPP='.$sebelum);
        }
    }

    public function test_the_version_label_shows_on_the_landing_page_and_the_dashboard(): void
    {
        config(['edulink.versi' => 'v0.9 Beta']);

        $this->get(route('landing'))->assertSeeText('EduLink Tutor v0.9 Beta');
        $this->actingAs(AkunPengguna::factory()->ortu()->create())->get(route('beranda'))->assertSeeText('EduLink Tutor v0.9 Beta');
    }

    public function test_pages_link_every_icon(): void
    {
        $this->get(route('landing'))
            ->assertSee('href="'.asset('favicon.ico').'"', false)
            ->assertSee('href="'.asset('favicon.svg').'"', false)
            ->assertSee('<link rel="apple-touch-icon" href="'.asset('apple-touch-icon.png').'">', false);

        $this->assertFileExists(public_path('apple-touch-icon.png'));
        $this->assertSame([180, 180], array_slice(getimagesize(public_path('apple-touch-icon.png')), 0, 2));
    }
}
