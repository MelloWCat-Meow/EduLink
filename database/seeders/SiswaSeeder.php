<?php

namespace Database\Seeders;

use App\Models\AkunPengguna;
use Illuminate\Database\Seeder;

/**
 * Two children for the first guardian and one for the second.
 * Runs after AkunPenggunaSeeder.
 */
class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        $children = [
            'siti.aminah@edulink.test' => [
                ['nama' => 'Nadia Putri', 'jenjang' => 'SD'],
                ['nama' => 'Raka Pratama', 'jenjang' => 'SMP'],
            ],
            'budi.santoso@edulink.test' => [
                ['nama' => 'Dimas Saputra', 'jenjang' => 'SMA'],
            ],
        ];

        foreach ($children as $kontakWali => $siswa) {
            $wali = AkunPengguna::query()->where('kontak', $kontakWali)->firstOrFail();

            foreach ($siswa as $anak) {
                $wali->siswa()->create([...$anak, 'kontak_wali' => $wali->kontak]);
            }
        }
    }
}
