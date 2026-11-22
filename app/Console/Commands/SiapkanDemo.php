<?php

namespace App\Console\Commands;

use App\Support\BerkasContoh;
use Database\Seeders\TutorSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

#[Signature('edulink:siapkan-demo {--force : Lewati pertanyaan konfirmasi}')]
#[Description('Siapkan ulang database dengan data demo untuk presentasi (khusus mode demo)')]
class SiapkanDemo extends Command
{
    /**
     * The accounts to sign in with during the presentation, in the order they
     * are used. Kept in step with the seeders by SiapkanDemoTest.
     *
     * @var list<array{0: string, 1: string, 2: string, 3: string}>
     */
    public const AKUN = [
        ['Admin', 'admin@edulink.test', 'admin12345', 'Ringkasan, verifikasi, keluhan, pencairan'],
        ['Orang tua', 'siti.aminah@edulink.test', 'orangtua123', 'Memesan, membayar, konfirmasi sesi'],
        ['Orang tua', 'budi.santoso@edulink.test', 'orangtua123', 'Sesi kemarin menunggu konfirmasi'],
        ['Tutor', 'andi.pratama@edulink.test', TutorSeeder::KATA_SANDI, 'Menerima pesanan, tautan meeting'],
        ['Tutor', 'rina.marlina@edulink.test', TutorSeeder::KATA_SANDI, 'Mencatat sesi, mengajukan pencairan'],
        ['Tutor', 'dewi.anggraini@edulink.test', TutorSeeder::KATA_SANDI, 'Sesi hari ini dan besok'],
        ['Pendaftar', 'nurul.aisyah@edulink.test', TutorSeeder::KATA_SANDI, 'Menunggu verifikasi admin'],
    ];

    public function handle(): int
    {
        if (! config('edulink.mode_demo')) {
            $this->components->error('Perintah ini hanya boleh dijalankan di mode demo (EDULINK_MODE_DEMO=true), karena menghapus seluruh data.');

            return self::FAILURE;
        }

        $database = DB::connection()->getDatabaseName();

        if (! $this->option('force') && ! $this->confirm("Semua data di database \"{$database}\" akan dihapus dan diganti data demo. Lanjutkan?", false)) {
            $this->components->info('Dibatalkan. Tidak ada yang diubah.');

            return self::SUCCESS;
        }

        // Sample tutor documents from the previous run would be left without owners.
        Storage::disk('local')->deleteDirectory(BerkasContoh::FOLDER);

        foreach ([
            'Membuat ulang tabel dan data demo' => ['migrate:fresh', ['--seed' => true, '--force' => true]],
            'Membersihkan cache' => ['optimize:clear', []],
        ] as $langkah => [$perintah, $argumen]) {
            $berhasil = false;
            $this->components->task($langkah, function () use ($perintah, $argumen, &$berhasil): bool {
                return $berhasil = $this->callSilently($perintah, $argumen) === self::SUCCESS;
            });

            if (! $berhasil) {
                $this->components->error("Langkah \"{$langkah}\" gagal. Jalankan php artisan {$perintah} untuk melihat pesan lengkapnya.");

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->components->info('Data demo siap, relatif terhadap '.now()->translatedFormat('l, j F Y H.i').'.');

        $this->table(['Peran', 'Email', 'Kata sandi', 'Dipakai untuk'], self::AKUN);

        $this->components->twoColumnDetail('<fg=gray>Halaman penting</>');
        foreach ([
            'Landing page' => route('landing'),
            'Masuk' => route('login'),
            'Cari tutor' => route('tutor.index'),
            'Daftar jadi tutor' => route('tutor.register'),
            'Aktivasi tutor internal' => route('tutor.aktivasi'),
            'Ringkasan admin' => route('admin.beranda'),
        ] as $nama => $url) {
            $this->components->twoColumnDetail($nama, $url);
        }

        $this->newLine();
        $this->line('  Percepat konfirmasi otomatis sesi: <fg=yellow>php artisan edulink:konfirmasi-otomatis --setelah=0</>');
        $this->line('  Alamat di atas memakai APP_URL ('.config('app.url').'). Untuk ngrok, isi APP_URL dengan alamat ngrok di .env.');

        return self::SUCCESS;
    }
}
