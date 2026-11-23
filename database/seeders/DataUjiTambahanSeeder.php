<?php

namespace Database\Seeders;

use App\Enums\JenisDokumen;
use App\Enums\Peran;
use App\Enums\StatusTutor;
use App\Enums\StatusVerifikasi;
use App\Enums\StatusVerifikasiTutor;
use App\Models\AkunPengguna;
use App\Models\MataPelajaran;
use App\Models\Tutor;
use App\Support\BerkasContoh;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Data tiruan tambahan agar setiap tabel master berisi minimal 30 baris
 * (Bagian 4.4.2 laporan) dan target KPI UAT "30 tutor valid" tercapai.
 *
 * Jumlah setelah DatabaseSeeder + seeder ini:
 *   mata_pelajaran : 18 + 12 = 30
 *   akun_pengguna  : 10 + 20 = 30  (8 orang tua baru + 12 akun tutor eksternal)
 *   siswa          :  3 + 27 = 30
 *   tutor          : 14 + 22 = 36  (30 berstatus terverifikasi)
 *
 * Jalankan SETELAH seeder utama:
 *   php artisan migrate:fresh --seed
 *   php artisan db:seed --class=DataUjiTambahanSeeder
 *
 * Seluruh data fiktif (email @edulink.test), sesuai DoD-B6.
 */
class DataUjiTambahanSeeder extends Seeder
{
    private const KATA_SANDI_ORTU = 'orangtua123';

    public function run(): void
    {
        $this->mataPelajaran();
        $this->orangTuaDanSiswa();
        $this->tutor();
    }

    /** 12 mata pelajaran baru: total 30 baris. */
    private function mataPelajaran(): void
    {
        $baru = [
            ['PKN-SD', 'PPKn', 'SD'],
            ['PKN-SMP', 'PPKn', 'SMP'],
            ['PAI-SD', 'Pendidikan Agama Islam', 'SD'],
            ['PAI-SMP', 'Pendidikan Agama Islam', 'SMP'],
            ['SBK-SD', 'Seni Budaya', 'SD'],
            ['SBK-SMP', 'Seni Budaya', 'SMP'],
            ['INF-SMP', 'Informatika', 'SMP'],
            ['INF-SMA', 'Informatika', 'SMA'],
            ['SEJ-SMA', 'Sejarah', 'SMA'],
            ['GEO-SMA', 'Geografi', 'SMA'],
            ['EKO-SMA', 'Ekonomi', 'SMA'],
            ['SOS-SMA', 'Sosiologi', 'SMA'],
        ];

        foreach ($baru as [$kode, $nama, $jenjang]) {
            MataPelajaran::query()->firstOrCreate(
                ['kode_mapel' => $kode],
                ['nama' => $nama, 'jenjang' => $jenjang],
            );
        }
    }

    /** 8 akun orang tua dengan total 27 siswa. */
    private function orangTuaDanSiswa(): void
    {
        $keluarga = [
            ['Agus Setiawan', [['Alya Setiawan', 'SD'], ['Bima Setiawan', 'SMP'], ['Cinta Setiawan', 'SMA']]],
            ['Ratna Sari', [['Dafa Pratama', 'SD'], ['Elsa Pratama', 'SD'], ['Fikri Pratama', 'SMP']]],
            ['Hendri Gunawan', [['Gilang Gunawan', 'SMA'], ['Hana Gunawan', 'SMP'], ['Ilham Gunawan', 'SD']]],
            ['Lina Marlina', [['Jihan Marlina', 'SMP'], ['Kenzo Marlina', 'SMA'], ['Laila Marlina', 'SD']]],
            ['Mulyadi Hartono', [['Mira Hartono', 'SMA'], ['Naufal Hartono', 'SMP'], ['Okta Hartono', 'SD']]],
            ['Novi Andriani', [['Putra Andriani', 'SD'], ['Qonita Andriani', 'SMP'], ['Rafi Andriani', 'SMA'], ['Salsa Andriani', 'SD']]],
            ['Oktavianus Lubis', [['Tegar Lubis', 'SMA'], ['Ulfa Lubis', 'SMP'], ['Vina Lubis', 'SD'], ['Wahyu Lubis', 'SMA']]],
            ['Priska Damanik', [['Xena Damanik', 'SD'], ['Yoga Damanik', 'SMP'], ['Zahra Damanik', 'SMA'], ['Arif Damanik', 'SMP']]],
        ];

        foreach ($keluarga as $i => [$namaWali, $anak]) {
            $wali = AkunPengguna::forceCreate([
                'nama' => $namaWali,
                'kontak' => Str::slug($namaWali, '.').'@edulink.test',
                'no_hp' => '0813772002'.str_pad((string) ($i + 10), 2, '0', STR_PAD_LEFT),
                'peran' => Peran::Ortu,
                'kredensial' => self::KATA_SANDI_ORTU,
                'persetujuan_pada' => now(),
                'versi_kebijakan' => config('edulink.versi_kebijakan'),
            ]);

            foreach ($anak as [$namaAnak, $jenjang]) {
                $wali->siswa()->create([
                    'nama' => $namaAnak,
                    'jenjang' => $jenjang,
                    'kontak_wali' => $wali->kontak,
                ]);
            }
        }
    }

    /** 22 tutor terverifikasi baru (12 eksternal berakun, 10 internal). */
    private function tutor(): void
    {
        $admin = AkunPengguna::query()->where('peran', Peran::Admin)->orderBy('id_akun')->firstOrFail();

        // [nama, status, mapel, tarif, lokasi, online, kunjungan, institusi, jurusan, tahun mengajar]
        $daftar = [
            ['Ahmad Fauzi', 'e', ['MTK-SMP', 'MTK-SMA'], 95000, 'Telanaipura', true, true, 'Universitas Jambi', 'Pendidikan Matematika', 5],
            ['Citra Maharani', 'e', ['BIN-SMP', 'BIN-SMA'], 70000, 'Kota Baru', true, false, 'Universitas Jambi', 'Pendidikan Bahasa Indonesia', 4],
            ['Doni Kurniawan', 'e', ['INF-SMP', 'INF-SMA'], 85000, 'Alam Barajo', true, true, 'Universitas Jambi', 'Sistem Informasi', 3],
            ['Eka Putri Lestari', 'e', ['SEJ-SMA', 'PKN-SMP'], 65000, 'Jelutung', true, true, 'UIN Sulthan Thaha Saifuddin Jambi', 'Pendidikan Sejarah', 4],
            ['Farhan Alamsyah', 'e', ['KIM-SMA', 'IPA-SMP'], 90000, 'Paal Merah', true, true, 'Universitas Jambi', 'Kimia', 6],
            ['Gita Ramadhani', 'e', ['BIG-SD', 'BIG-SMP'], 60000, 'Pasar Jambi', true, true, 'Universitas Batanghari', 'Pendidikan Bahasa Inggris', 3],
            ['Hasan Basri', 'e', ['EKO-SMA', 'SOS-SMA'], 75000, 'Jambi Selatan', false, true, 'Universitas Jambi', 'Pendidikan Ekonomi', 5],
            ['Indri Septiani', 'e', ['IPA-SD', 'MTK-SD'], 55000, 'Danau Sipin', true, true, 'Universitas Jambi', 'PGSD', 2],
            ['Joko Susilo', 'e', ['FIS-SMA', 'MTK-SMA'], 105000, 'Telanaipura', true, false, 'Universitas Gadjah Mada', 'Fisika', 7],
            ['Kartika Sari', 'e', ['GEO-SMA', 'IPS-SMP'], 70000, 'Kota Baru', true, true, 'Universitas Jambi', 'Pendidikan Geografi', 4],
            ['Lutfi Hakim', 'e', ['PAI-SD', 'PAI-SMP'], 50000, 'Pelayangan', false, true, 'UIN Sulthan Thaha Saifuddin Jambi', 'Pendidikan Agama Islam', 3],
            ['Mega Anjani', 'e', ['SBK-SD', 'SBK-SMP'], 55000, 'Jambi Timur', true, true, 'Universitas Negeri Padang', 'Pendidikan Seni Rupa', 2],
            ['Nanda Pratiwi', 'i', ['MTK-SMP', 'IPA-SMP'], 80000, 'Telanaipura', true, true, 'Universitas Jambi', 'Pendidikan Matematika', 5],
            ['Oki Setiawan', 'i', ['FIS-SMA', 'UTBK-SMA'], 110000, 'Alam Barajo', true, true, 'Universitas Jambi', 'Fisika', 6],
            ['Putri Rahayu', 'i', ['CAL-SD', 'PKN-SD'], 55000, 'Jelutung', false, true, 'Universitas Jambi', 'PGSD', 4],
            ['Qori Ananda', 'i', ['BIO-SMA', 'IPA-SMP'], 85000, 'Kota Baru', true, true, 'Universitas Jambi', 'Biologi', 5],
            ['Rizky Maulana', 'i', ['BIG-SMA', 'UTBK-SMA'], 100000, 'Paal Merah', true, true, 'Universitas Jambi', 'Sastra Inggris', 6],
            ['Sinta Dewi', 'i', ['IPS-SD', 'PKN-SD', 'PAI-SD'], 55000, 'Jambi Selatan', false, true, 'Universitas Jambi', 'PGSD', 3],
            ['Taufik Hidayah', 'i', ['SEJ-SMA', 'PKN-SMP'], 70000, 'Pasar Jambi', true, true, 'Universitas Jambi', 'Pendidikan Sejarah', 4],
            ['Umi Kalsum', 'i', ['BIN-SD', 'CAL-SD'], 55000, 'Danau Sipin', false, true, 'Universitas Jambi', 'PGSD', 7],
            ['Vino Alfarizi', 'i', ['MTK-SMA', 'INF-SMA'], 100000, 'Jambi Timur', true, true, 'Universitas Jambi', 'Teknik Informatika', 3],
            ['Winda Oktaviani', 'i', ['EKO-SMA', 'GEO-SMA', 'SOS-SMA'], 80000, 'Pelayangan', true, true, 'Universitas Jambi', 'Pendidikan Ekonomi', 5],
        ];

        $hari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        $jam = ['15:00', '16:00', '19:00', '10:00'];

        foreach ($daftar as $i => [$nama, $jenis, $mapel, $tarif, $lokasi, $online, $kunjungan, $institusi, $jurusan, $tahun]) {
            $eksternal = $jenis === 'e';
            $kontak = Str::slug($nama, '.').'@edulink.test';
            $noHp = '0812661003'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);

            $akun = null;
            if ($eksternal) {
                $akun = AkunPengguna::forceCreate([
                    'nama' => $nama,
                    'kontak' => $kontak,
                    'no_hp' => $noHp,
                    'peran' => Peran::Tutor,
                    'kredensial' => TutorSeeder::KATA_SANDI,
                    'persetujuan_pada' => now(),
                    'versi_kebijakan' => config('edulink.versi_kebijakan'),
                ]);
            }

            $tutor = new Tutor([
                'id_akun' => $akun?->id_akun,
                'nama' => $nama,
                'status' => $eksternal ? StatusTutor::Eksternal : StatusTutor::Internal,
                'kontak' => $kontak,
                'no_hp' => $noHp,
            ]);
            $tutor->status_verifikasi = StatusVerifikasiTutor::Terverifikasi;
            $tutor->save();

            $namaMapel = MataPelajaran::query()->whereIn('kode_mapel', $mapel)->pluck('nama')->unique()->implode(', ');

            $tutor->profil()->create([
                'riwayat_pendidikan' => ['pendidikan' => 'S1', 'institusi' => $institusi, 'jurusan' => $jurusan],
                'kompetensi' => "S1 {$jurusan}. Mengajar {$namaMapel} dengan latihan soal bertahap dan pembahasan yang sabar.",
                'pengalaman' => "{$tahun} tahun mengajar privat dan kelas kecil di Kota Jambi.",
                'tarif' => $tarif,
                'lokasi' => $lokasi,
                'melayani_online' => $online,
                'melayani_kunjungan' => $kunjungan,
            ]);

            $tutor->mataPelajaran()->attach($mapel);

            // Dua slot per tutor pada hari berbeda, sehingga unik per (tutor, hari, jam).
            foreach ([[$hari[$i % 6], $jam[$i % 4]], [$hari[($i + 3) % 6], $jam[($i + 1) % 4]]] as [$h, $j]) {
                $tutor->jadwal()->create(['hari' => $h, 'jam' => $j.':00', 'status' => 'tersedia']);
            }

            // Tutor eksternal wajib punya dokumen valid; tutor internal berasal dari data perusahaan.
            if ($eksternal) {
                foreach (JenisDokumen::cases() as $jenisDokumen) {
                    $tutor->dokumen()->create([
                        'jenis' => $jenisDokumen,
                        'berkas' => BerkasContoh::simpan($tutor->id_tutor, $jenisDokumen->label().' - '.$tutor->nama),
                        'status_verifikasi' => StatusVerifikasi::Valid,
                        'diperiksa_pada' => now()->subMonth(),
                    ]);
                }
            }

            $tutor->riwayatVerifikasi()->create([
                'status' => StatusVerifikasiTutor::Terverifikasi,
                'id_akun' => $admin->id_akun,
                'dicatat_pada' => now()->subMonth(),
            ]);
        }
    }
}