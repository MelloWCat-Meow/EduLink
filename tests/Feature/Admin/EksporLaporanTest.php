<?php

namespace Tests\Feature\Admin;

use App\Models\AkunPengguna;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class EksporLaporanTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    private AkunPengguna $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = AkunPengguna::factory()->admin()->create();
    }

    public function test_csv_holds_the_records_of_the_chosen_period_in_an_excel_friendly_format(): void
    {
        $this->travelTo(Carbon::parse('2026-10-15 09:00'));
        $oktober = $this->pesananDibayar();

        $this->travelTo(Carbon::parse('2026-11-10 09:00'));
        $wali = AkunPengguna::factory()->ortu()->create(['nama' => '=HYPERLINK("http://jahat")']);
        $november = $this->pesananDibayar(Siswa::factory()->for($wali, 'wali')->create(['nama' => 'Nadia; Putri']));
        $this->actingAs($this->admin)->post(route('admin.pesanan.batal', $november), ['alasan' => 'Tutor pindah kota.']);

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.csv', ['periode' => '2026-11']));

        $response->assertOk();
        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('laporan-edulink-2026-11.csv', $response->headers->get('Content-Disposition'));

        $csv = $response->getContent();
        $this->assertStringStartsWith("\u{FEFF}", $csv);
        $this->assertStringContainsString("\r\n", $csv);
        $this->assertStringContainsString($november->id_pesanan, $csv);
        $this->assertStringNotContainsString($oktober->id_pesanan, $csv);

        foreach (['PESANAN', 'PEMBAYARAN BERHASIL', 'KOMISI PLATFORM', 'PENCAIRAN DANA TUTOR', 'PENGEMBALIAN DANA'] as $bagian) {
            $this->assertStringContainsString($bagian, $csv);
        }

        // Whole rupiah, no thousands separator; a field with the separator is quoted; formulas are defused.
        $this->assertMatchesRegularExpression('/'.preg_quote($november->id_pesanan, '/').';[^\r\n]*;'.(int) $november->total_biaya.'\r\n/', $csv);
        $this->assertStringContainsString('"Nadia; Putri"', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString(';=HYPERLINK', $csv);
        $this->assertStringNotContainsString('Rp', str_replace('(Rp)', '', $csv));
    }

    public function test_print_page_shows_the_period_summary(): void
    {
        $this->travelTo(Carbon::parse('2026-11-10 09:00'));
        $pesanan = $this->pesananDibayar();

        $this->actingAs($this->admin)->get(route('admin.laporan.cetak', ['periode' => '2026-11']))
            ->assertOk()
            ->assertSeeText('Laporan November 2026')
            ->assertSeeText($pesanan->id_pesanan)
            ->assertSee('window.print()', false);

        $this->actingAs($this->admin)->get(route('admin.beranda', ['periode' => '2026-11']))
            ->assertSee(route('admin.laporan.csv', ['periode' => '2026-11']), false)
            ->assertSee(route('admin.laporan.cetak', ['periode' => '2026-11']), false);
    }

    public function test_only_admins_can_export(): void
    {
        $this->get(route('admin.laporan.csv'))->assertRedirect(route('login'));
        $this->actingAs(AkunPengguna::factory()->ortu()->create())->get(route('admin.laporan.csv'))->assertForbidden();
        $this->actingAs(AkunPengguna::factory()->ortu()->create())->get(route('admin.laporan.cetak'))->assertForbidden();
    }

    public function test_an_invalid_period_falls_back_to_the_current_month(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.csv', ['periode' => '2026-13']));

        $this->assertStringContainsString('laporan-edulink-'.now()->format('Y-m').'.csv', $response->headers->get('Content-Disposition'));
    }
}
