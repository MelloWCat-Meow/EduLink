<?php

namespace Database\Seeders;

use App\Enums\Peran;
use App\Models\AkunPengguna;
use Illuminate\Database\Seeder;

/**
 * Demo accounts. The admin is created first so it gets AK0001.
 * The passwords are for local demos only; never seed them in production.
 */
class AkunPenggunaSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['nama' => 'Dewi Lestari', 'kontak' => 'admin@edulink.test', 'peran' => Peran::Admin, 'kredensial' => 'admin12345'],
            ['nama' => 'Siti Aminah', 'kontak' => 'siti.aminah@edulink.test', 'peran' => Peran::Ortu, 'kredensial' => 'orangtua123'],
            ['nama' => 'Budi Santoso', 'kontak' => 'budi.santoso@edulink.test', 'peran' => Peran::Ortu, 'kredensial' => 'orangtua123'],
        ];

        foreach ($accounts as $account) {
            AkunPengguna::forceCreate($account);
        }
    }
}
