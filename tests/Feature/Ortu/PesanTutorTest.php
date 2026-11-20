<?php

namespace Tests\Feature\Ortu;

use App\Enums\StatusJadwal;
use App\Enums\StatusPesanan;
use App\Exceptions\JadwalTerisi;
use App\Models\AkunPengguna;
use App\Models\MataPelajaran;
use App\Models\Pesanan;
use App\Models\Siswa;
use App\Models\Tutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PesanTutorTest extends TestCase
{
    use RefreshDatabase;

    private Tutor $tutor;

    private AkunPengguna $ortu;

    private Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-11-20 10:00'));

        $mapel = MataPelajaran::factory()->create(['kode_mapel' => 'MTK-SMP', 'nama' => 'Matematika', 'jenjang' => 'SMP']);
        $this->tutor = Tutor::factory()->siapDipesan(['tarif' => '80000.00', 'melayani_kunjungan' => false])->create();
        $this->tutor->mataPelajaran()->sync([$mapel->kode_mapel]);
        $this->tutor->jadwal()->update(['hari' => 'senin', 'jam' => '16:00:00']);

        $this->ortu = AkunPengguna::factory()->ortu()->create();
        $this->siswa = Siswa::factory()->for($this->ortu, 'wali')->create(['jenjang' => 'SMP']);
    }

    /**
     * @return array<string, mixed>
     */
    private function isian(array $ubah = []): array
    {
        return [
            'id_siswa' => $this->siswa->id_siswa,
            'kode_mapel' => 'MTK-SMP',
            'id_jadwal' => $this->tutor->jadwal()->value('id_jadwal'),
            'mode' => 'online',
            'durasi' => 90,
            'tanggal_mulai' => '2026-11-23',
            'jumlah_pertemuan' => 4,
            ...$ubah,
        ];
    }

    public function test_guest_is_sent_to_sign_in_and_returns_to_the_booking_form(): void
    {
        $this->get(route('tutor.pesan', $this->tutor))->assertRedirect(route('login'));

        $this->post(route('login.store'), ['kontak' => $this->ortu->kontak, 'password' => 'password'])
            ->assertRedirect(route('tutor.pesan', $this->tutor));
    }

    public function test_checking_the_order_shows_the_total_without_creating_it(): void
    {
        $this->actingAs($this->ortu)->post(route('tutor.pesan.store', $this->tutor), $this->isian())
            ->assertOk()
            ->assertSeeText('Periksa pesanan')
            ->assertSeeText('Rp480.000')
            ->assertSeeText('Konfirmasi pesanan');

        $this->assertDatabaseCount('pesanan', 0);
    }

    public function test_confirming_creates_the_order_and_holds_the_slot(): void
    {
        $response = $this->actingAs($this->ortu)->post(route('tutor.pesan.store', $this->tutor), $this->isian(['konfirmasi' => 1]));

        $pesanan = Pesanan::query()->sole();
        $response->assertRedirect(route('pesanan.show', $pesanan));
        $this->assertSame(StatusPesanan::MenungguKonfirmasiTutor, $pesanan->status);
        $this->assertSame('480000.00', $pesanan->total_biaya);
        $this->assertSame(StatusJadwal::Ditahan, $this->tutor->jadwal()->first()->status);
    }

    public function test_taken_slot_is_reported_with_the_jadwal_terisi_code(): void
    {
        $this->tutor->jadwal()->update(['status' => 'ditahan']);

        $this->actingAs($this->ortu)
            ->from(route('tutor.pesan', $this->tutor))
            ->post(route('tutor.pesan.store', $this->tutor), $this->isian(['konfirmasi' => 1]))
            ->assertRedirect(route('tutor.pesan', $this->tutor))
            ->assertSessionHasErrors(['id_jadwal' => 'Jadwal sudah terisi. Pilih jam lain yang masih tersedia.'])
            ->assertSessionHas('kode_galat', JadwalTerisi::KODE);

        $this->assertDatabaseCount('pesanan', 0);
    }

    public function test_taken_slot_returns_the_error_code_as_json(): void
    {
        $this->tutor->jadwal()->update(['status' => 'terisi']);

        $this->actingAs($this->ortu)
            ->postJson(route('tutor.pesan.store', $this->tutor), $this->isian(['konfirmasi' => 1]))
            ->assertStatus(409)
            ->assertJson(['kode' => 'JADWAL_TERISI']);
    }

    public function test_booking_rules_are_checked_with_indonesian_messages(): void
    {
        $this->actingAs($this->ortu)->post(route('tutor.pesan.store', $this->tutor), $this->isian([
            'mode' => 'kunjungan_rumah',
            'tanggal_mulai' => '2026-11-24',
            'jumlah_pertemuan' => 20,
        ]))->assertSessionHasErrors([
            'mode' => 'Tutor ini tidak melayani cara belajar tersebut.',
            'tanggal_mulai' => 'Tanggal mulai harus hari Senin, sesuai jadwal yang dipilih.',
            'jumlah_pertemuan' => 'Maksimal 12 pertemuan dalam satu pesanan.',
        ]);
    }

    public function test_subject_must_match_the_childs_level(): void
    {
        $this->siswa->update(['jenjang' => 'SD']);

        $this->actingAs($this->ortu)->post(route('tutor.pesan.store', $this->tutor), $this->isian())
            ->assertSessionHasErrors(['kode_mapel' => "Mapel ini untuk jenjang SMP, sedangkan {$this->siswa->nama} di SD."]);
    }

    public function test_parent_cannot_book_for_another_accounts_child(): void
    {
        $anakLain = Siswa::factory()->create(['jenjang' => 'SMP']);

        $this->actingAs($this->ortu)->post(route('tutor.pesan.store', $this->tutor), $this->isian(['id_siswa' => $anakLain->id_siswa, 'konfirmasi' => 1]))
            ->assertSessionHasErrors('id_siswa');

        $this->assertDatabaseCount('pesanan', 0);
    }

    public function test_unlisted_tutor_cannot_be_booked(): void
    {
        $tutor = Tutor::factory()->eksternal()->menunggu()->siapDipesan()->create();

        $this->actingAs($this->ortu)->get(route('tutor.pesan', $tutor))->assertNotFound();
    }
}
