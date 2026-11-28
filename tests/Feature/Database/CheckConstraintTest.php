<?php

namespace Tests\Feature\Database;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

/**
 * The CHECK constraints exist on MySQL/MariaDB only (SQLite cannot add them to
 * an existing table), so this runs against a MySQL connection, e.g.
 * DB_CONNECTION=mysql DB_DATABASE=edulink_test php artisan test --filter=CheckConstraintTest
 */
class CheckConstraintTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    protected bool $dropViews = true;

    protected function setUp(): void
    {
        parent::setUp();

        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('CHECK constraint hanya dipasang di MySQL/MariaDB.');
        }
    }

    public function test_rating_above_five_is_rejected(): void
    {
        $pesanan = $this->pesananSelesai();

        $this->expectException(QueryException::class);

        $pesanan->ulasan()->create(['nilai' => 6]);
    }

    public function test_unknown_order_status_is_rejected(): void
    {
        $pesanan = $this->pesananBaru();

        $this->expectException(QueryException::class);

        DB::table('pesanan')->where('id_pesanan', $pesanan->id_pesanan)->update(['status' => 'asal']);
    }

    public function test_zero_payment_amount_is_rejected(): void
    {
        $pembayaran = $this->pesananDibayar()->pembayaran()->firstOrFail();

        $this->expectException(QueryException::class);

        DB::table('pembayaran')->where('id_pembayaran', $pembayaran->id_pembayaran)->update(['jumlah' => 0]);
    }

    public function test_commission_above_one_hundred_percent_is_rejected(): void
    {
        $komisi = $this->pesananDibayar()->komisi()->firstOrFail();

        $this->expectException(QueryException::class);

        DB::table('komisi_biaya_layanan')->where('id_komisi', $komisi->id_komisi)->update(['persentase' => 150]);
    }

    public function test_valid_rows_still_pass(): void
    {
        $pesanan = $this->pesananSelesai();
        $pesanan->ulasan()->create(['nilai' => 5]);

        DB::table('komisi_biaya_layanan')->where('id_pesanan', $pesanan->id_pesanan)->update(['persentase' => 0, 'nominal' => 0]);

        $this->assertDatabaseHas('rating_ulasan', ['id_pesanan' => $pesanan->id_pesanan, 'nilai' => 5]);
    }
}
