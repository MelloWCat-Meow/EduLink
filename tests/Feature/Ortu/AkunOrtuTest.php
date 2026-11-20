<?php

namespace Tests\Feature\Ortu;

use App\Models\AkunPengguna;
use App\Models\Tutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AkunOrtuTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_without_a_phone_sees_a_reminder_and_can_fill_it_in(): void
    {
        $ortu = AkunPengguna::factory()->ortu()->create(['no_hp' => null]);

        $this->actingAs($ortu)->get(route('beranda'))->assertSeeText('Nomor HP belum diisi.')->assertSee(route('akun.edit'), false);

        $this->actingAs($ortu)->put(route('akun.update'), ['nama' => 'Siti Aminah', 'no_hp' => '+62 813-7720-0101'])
            ->assertRedirect(route('akun.edit'));

        $this->assertSame('+6281377200101', $ortu->refresh()->no_hp);
        $this->actingAs($ortu)->get(route('beranda'))->assertDontSeeText('Nomor HP belum diisi.');
    }

    public function test_account_page_validates_the_phone_and_keeps_the_email(): void
    {
        $ortu = AkunPengguna::factory()->ortu()->create(['kontak' => 'siti@contoh.com']);

        $this->actingAs($ortu)->put(route('akun.update'), ['nama' => 'Siti', 'no_hp' => 'abc', 'kontak' => 'lain@contoh.com'])
            ->assertSessionHasErrors('no_hp');
        $this->actingAs($ortu)->put(route('akun.update'), ['nama' => 'Siti', 'no_hp' => '081377200101', 'kontak' => 'lain@contoh.com']);

        $this->assertSame('siti@contoh.com', $ortu->refresh()->kontak);
    }

    public function test_account_page_is_only_for_parents(): void
    {
        $this->actingAs(AkunPengguna::factory()->admin()->create())->get(route('akun.edit'))->assertForbidden();
        $this->actingAs(Tutor::factory()->denganAkun()->create()->akun)->get(route('akun.edit'))->assertForbidden();
    }

    public function test_payout_account_number_is_stored_encrypted_and_masked(): void
    {
        $tutor = Tutor::factory()->create();
        $tutor->forceFill(['rekening_bank' => 'BRI', 'rekening_nomor' => '0123456789014417', 'rekening_atas_nama' => 'Rina'])->save();

        $this->assertSame('••••4417', $tutor->rekeningTersamar());
        $this->assertTrue($tutor->punyaRekening());
        $this->assertDatabaseMissing('tutor', ['rekening_nomor' => '0123456789014417']);
        $this->assertSame('0123456789014417', $tutor->refresh()->rekening_nomor);
    }
}
