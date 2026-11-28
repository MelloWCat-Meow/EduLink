<?php

use App\Enums\StatusJadwal;
use App\Enums\StatusPembayaran;
use App\Enums\StatusPesanan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * CHECK constraints that back up the application's validation, so a row
     * written outside the app still cannot hold an impossible value. Status
     * lists come from the enums and stay in step with the code.
     *
     * MySQL/MariaDB only: SQLite (used by the tests) cannot add a constraint to
     * an existing table, so the migration does nothing there.
     */
    public function up(): void
    {
        if (! $this->didukung()) {
            return;
        }

        foreach ($this->aturan() as $nama => [$tabel, $kondisi]) {
            DB::statement("ALTER TABLE {$tabel} ADD CONSTRAINT {$nama} CHECK ({$kondisi})");
        }
    }

    public function down(): void
    {
        if (! $this->didukung()) {
            return;
        }

        foreach (array_reverse($this->aturan()) as $nama => [$tabel]) {
            DB::statement("ALTER TABLE {$tabel} DROP CONSTRAINT {$nama}");
        }
    }

    private function didukung(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }

    /**
     * @return array<string, array{0: string, 1: string}> constraint name => [table, condition]
     */
    private function aturan(): array
    {
        return [
            'chk_rating_nilai' => ['rating_ulasan', 'nilai BETWEEN 1 AND 5'],
            'chk_jadwal_status' => ['jadwal_ketersediaan', 'status IN ('.$this->daftar(StatusJadwal::cases()).')'],
            'chk_pesanan_status' => ['pesanan', 'status IN ('.$this->daftar(StatusPesanan::cases()).')'],
            'chk_pesanan_angka' => ['pesanan', 'durasi > 0 AND jumlah_pertemuan > 0 AND tarif_per_jam > 0 AND total_biaya > 0'],
            'chk_pembayaran_status' => ['pembayaran', 'status IN ('.$this->daftar(StatusPembayaran::cases()).')'],
            'chk_pembayaran_jumlah' => ['pembayaran', 'jumlah > 0'],
            // A 0% commission is allowed: the admin may set 0 (KomisiRequest min:0).
            'chk_komisi_angka' => ['komisi_biaya_layanan', 'persentase BETWEEN 0 AND 100 AND nominal >= 0'],
            // NULL while an internal tutor has not filled in a rate yet.
            'chk_profil_tarif' => ['profil_tutor', 'tarif IS NULL OR tarif > 0'],
        ];
    }

    /**
     * @param  list<BackedEnum>  $kasus
     */
    private function daftar(array $kasus): string
    {
        return implode(', ', array_map(fn (string $nilai): string => DB::getPdo()->quote($nilai), array_column($kasus, 'value')));
    }
};
