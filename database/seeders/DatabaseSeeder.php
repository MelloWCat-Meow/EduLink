<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Model events stay on: AkunPengguna and Siswa generate their
     * AK0001 / S0001 keys in a "creating" listener.
     */
    public function run(): void
    {
        $this->call([
            AkunPenggunaSeeder::class,
            SiswaSeeder::class,
        ]);
    }
}
