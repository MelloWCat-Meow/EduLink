<?php

namespace Database\Seeders;

use App\Models\MataPelajaran;
use Illuminate\Database\Seeder;

class MataPelajaranSeeder extends Seeder
{
    /**
     * Subject codes are "<short name>-<level>", e.g. MTK-SMP.
     *
     * @var array<string, array{0: string, 1: list<string>}>
     */
    public const MAPEL = [
        'CAL' => ['Calistung', ['SD']],
        'MTK' => ['Matematika', ['SD', 'SMP', 'SMA']],
        'IPA' => ['IPA', ['SD', 'SMP']],
        'IPS' => ['IPS', ['SD', 'SMP']],
        'BIN' => ['Bahasa Indonesia', ['SD', 'SMP', 'SMA']],
        'BIG' => ['Bahasa Inggris', ['SD', 'SMP', 'SMA']],
        'FIS' => ['Fisika', ['SMA']],
        'KIM' => ['Kimia', ['SMA']],
        'BIO' => ['Biologi', ['SMA']],
        'UTBK' => ['Persiapan UTBK', ['SMA']],
    ];

    public function run(): void
    {
        foreach (self::MAPEL as $singkatan => [$nama, $daftarJenjang]) {
            foreach ($daftarJenjang as $jenjang) {
                MataPelajaran::query()->create([
                    'kode_mapel' => $singkatan.'-'.$jenjang,
                    'nama' => $nama,
                    'jenjang' => $jenjang,
                ]);
            }
        }
    }
}
