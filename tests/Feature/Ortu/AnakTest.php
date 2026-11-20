<?php

namespace Tests\Feature\Ortu;

use App\Models\AkunPengguna;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class AnakTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    public function test_parent_adds_a_child_and_the_guardian_contact_comes_from_the_account(): void
    {
        $ortu = AkunPengguna::factory()->ortu()->create(['kontak' => 'wali@contoh.com']);

        $this->actingAs($ortu)->post(route('anak.store'), ['nama' => '  Nadia Putri ', 'jenjang' => 'SD', 'kontak_wali' => 'lain@contoh.com'])
            ->assertRedirect(route('anak.index'));

        $this->assertDatabaseHas('siswa', ['id_akun' => $ortu->id_akun, 'nama' => 'Nadia Putri', 'jenjang' => 'SD', 'kontak_wali' => 'wali@contoh.com']);
    }

    public function test_child_form_reports_errors_in_indonesian(): void
    {
        $ortu = AkunPengguna::factory()->ortu()->create();

        $this->actingAs($ortu)->post(route('anak.store'), ['nama' => '', 'jenjang' => 'TK'])
            ->assertSessionHasErrors(['nama' => 'Nama anak belum diisi.', 'jenjang' => 'Pilih SD, SMP, atau SMA.']);
    }

    public function test_parent_updates_their_child(): void
    {
        $siswa = Siswa::factory()->create(['jenjang' => 'SD']);
        $ortu = AkunPengguna::query()->findOrFail($siswa->id_akun);

        $this->actingAs($ortu)->put(route('anak.update', $siswa), ['nama' => 'Nadia', 'jenjang' => 'SMP'])->assertRedirect(route('anak.index'));

        $this->assertSame('SMP', $siswa->refresh()->jenjang);
    }

    public function test_child_without_orders_can_be_deleted(): void
    {
        $siswa = Siswa::factory()->create();
        $ortu = AkunPengguna::query()->findOrFail($siswa->id_akun);

        $this->actingAs($ortu)->delete(route('anak.destroy', $siswa))->assertRedirect(route('anak.index'));

        $this->assertModelMissing($siswa);
    }

    public function test_child_with_an_active_order_cannot_be_deleted(): void
    {
        $siswa = Siswa::factory()->create();
        $this->pesananBaru($siswa);
        $ortu = AkunPengguna::query()->findOrFail($siswa->id_akun);

        $this->actingAs($ortu)->from(route('anak.index'))->delete(route('anak.destroy', $siswa))
            ->assertRedirect(route('anak.index'))
            ->assertSessionHas('galat');

        $this->assertModelExists($siswa);
    }

    public function test_parent_cannot_touch_another_accounts_child(): void
    {
        $milikLain = Siswa::factory()->create();
        $ortu = AkunPengguna::factory()->ortu()->create();

        $this->actingAs($ortu)->get(route('anak.edit', $milikLain))->assertForbidden();
        $this->actingAs($ortu)->put(route('anak.update', $milikLain), ['nama' => 'X', 'jenjang' => 'SD'])->assertForbidden();
        $this->actingAs($ortu)->delete(route('anak.destroy', $milikLain))->assertForbidden();

        $this->assertModelExists($milikLain);
    }

    public function test_child_list_shows_only_own_children(): void
    {
        $ortu = AkunPengguna::factory()->ortu()->create();
        Siswa::factory()->for($ortu, 'wali')->create(['nama' => 'Nadia Putri']);
        Siswa::factory()->create(['nama' => 'Anak Orang Lain']);

        $this->actingAs($ortu)->get(route('anak.index'))
            ->assertOk()
            ->assertSeeText('Nadia Putri')
            ->assertDontSeeText('Anak Orang Lain');
    }
}
