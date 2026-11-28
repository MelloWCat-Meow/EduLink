<?php

use App\Enums\StatusPembayaran;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Report views for querying the database directly (SQL client, report
     * appendix). The app does not read them; its pages compute the same figures
     * in App\Services\LaporanRingkasan and the Tutor::denganRating() scope.
     * Plain SQL that runs on MySQL/MariaDB and SQLite alike.
     *
     * migrate:fresh leaves views behind unless it is given --drop-views, so each
     * view is dropped before it is created.
     */
    public function up(): void
    {
        foreach ($this->view() as $nama => $query) {
            DB::statement("DROP VIEW IF EXISTS {$nama}");
            DB::statement("CREATE VIEW {$nama} AS {$query}");
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->view()) as $nama) {
            DB::statement("DROP VIEW IF EXISTS {$nama}");
        }
    }

    /**
     * @return array<string, string> view name => SELECT
     */
    private function view(): array
    {
        $berhasil = DB::getPdo()->quote(StatusPembayaran::Berhasil->value);
        $gagal = DB::getPdo()->quote(StatusPembayaran::Gagal->value);
        $pending = DB::getPdo()->quote(StatusPembayaran::Pending->value);

        return [
            // Successful payments by the day the gateway confirmed them, as on
            // the admin summary. total_komisi is the commission fixed when the
            // order was paid (komisi_biaya_layanan.nominal); an order has at
            // most one successful payment and one commission row.
            'v_laporan_transaksi_harian' => "
                SELECT DATE(b.diproses_pada) AS tanggal,
                       COUNT(*) AS jumlah_pembayaran,
                       SUM(b.jumlah) AS total_pembayaran,
                       COALESCE(SUM(k.nominal), 0) AS total_komisi
                FROM pembayaran b
                LEFT JOIN komisi_biaya_layanan k ON k.id_pesanan = b.id_pesanan
                WHERE b.status = {$berhasil} AND b.diproses_pada IS NOT NULL
                GROUP BY DATE(b.diproses_pada)",

            // Same rule as Tutor::denganRating(): only reviews the admin left
            // visible count. Tutors without one show NULL and 0.
            'v_rating_tutor' => '
                SELECT t.id_tutor,
                       t.nama,
                       AVG(u.nilai) AS rata_rating,
                       COUNT(u.id_ulasan) AS jumlah_ulasan
                FROM tutor t
                LEFT JOIN (
                    SELECT p.id_tutor, r.id_ulasan, r.nilai
                    FROM rating_ulasan r
                    JOIN pesanan p ON p.id_pesanan = r.id_pesanan
                    WHERE r.ditampilkan = 1
                ) u ON u.id_tutor = t.id_tutor
                GROUP BY t.id_tutor, t.nama',

            // Every payment attempt, whatever its outcome. One row.
            'v_tingkat_keberhasilan_pembayaran' => "
                SELECT COUNT(*) AS total_percobaan,
                       COALESCE(SUM(CASE WHEN status = {$berhasil} THEN 1 ELSE 0 END), 0) AS jumlah_berhasil,
                       COALESCE(SUM(CASE WHEN status = {$gagal} THEN 1 ELSE 0 END), 0) AS jumlah_gagal,
                       COALESCE(SUM(CASE WHEN status = {$pending} THEN 1 ELSE 0 END), 0) AS jumlah_pending,
                       CASE WHEN COUNT(*) = 0 THEN NULL
                            ELSE ROUND(100.0 * SUM(CASE WHEN status = {$berhasil} THEN 1 ELSE 0 END) / COUNT(*), 2)
                       END AS persen_berhasil
                FROM pembayaran",
        ];
    }
};
