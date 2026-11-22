<?php

namespace Database\Seeders;

use App\Enums\JenisDokumen;
use App\Enums\Peran;
use App\Enums\StatusTutor;
use App\Enums\StatusVerifikasi;
use App\Enums\StatusVerifikasiTutor;
use App\Models\AkunPengguna;
use App\Models\DokumenVerifikasi;
use App\Models\Tutor;
use App\Support\BerkasContoh;
use DateTimeInterface;
use Illuminate\Database\Seeder;

/**
 * Eight verified tutors (the first four are the sample cards on the landing
 * page), two applications waiting for the admin, and the sign-up demos: two
 * internal tutors without an account, one application sent back for
 * corrections and one draft that was never sent.
 *
 * Tutor accounts sign in with the password in KATA_SANDI.
 */
class TutorSeeder extends Seeder
{
    public const KATA_SANDI = 'tutor12345';

    /**
     * @var list<array{nama: string, status: StatusTutor, kontak: string, no_hp: string, verifikasi: StatusVerifikasiTutor, akun: bool, dokumen: StatusVerifikasi|null, mapel: list<string>, tarif: int|null, lokasi: string|null, online: bool, kunjungan: bool, pendidikan: array{pendidikan: string, institusi: string, jurusan: string}, kompetensi: string, pengalaman: string, jadwal: list<array{0: string, 1: string}>, diajukan_hari_lalu?: int}>
     */
    private function daftarTutor(): array
    {
        return [
            [
                'nama' => 'Rina Marlina', 'status' => StatusTutor::Eksternal, 'kontak' => 'rina.marlina@edulink.test', 'no_hp' => '081266100201',
                'verifikasi' => StatusVerifikasiTutor::Terverifikasi, 'akun' => true, 'dokumen' => StatusVerifikasi::Valid,
                'mapel' => ['MTK-SD', 'MTK-SMP', 'IPA-SD', 'IPA-SMP'], 'tarif' => 75000, 'lokasi' => 'Telanaipura', 'online' => true, 'kunjungan' => true,
                'pendidikan' => ['pendidikan' => 'S1', 'institusi' => 'Universitas Jambi', 'jurusan' => 'Pendidikan Matematika'],
                'kompetensi' => 'S1 Pendidikan Matematika. Terbiasa mengajar anak yang tertinggal di pecahan dan aljabar dasar dengan benda sehari-hari.',
                'pengalaman' => 'Enam tahun les privat SD dan SMP di Telanaipura dan Kota Baru.',
                'jadwal' => [['senin', '16:00'], ['rabu', '16:00'], ['sabtu', '09:00']],
            ],
            [
                'nama' => 'Fadli Ramadhan', 'status' => StatusTutor::Internal, 'kontak' => 'fadli.ramadhan@edulink.test', 'no_hp' => '081266100202',
                'verifikasi' => StatusVerifikasiTutor::Terverifikasi, 'akun' => false, 'dokumen' => null,
                'mapel' => ['FIS-SMA', 'UTBK-SMA'], 'tarif' => 110000, 'lokasi' => 'Alam Barajo', 'online' => true, 'kunjungan' => true,
                'pendidikan' => ['pendidikan' => 'S1', 'institusi' => 'Universitas Jambi', 'jurusan' => 'Fisika'],
                'kompetensi' => 'S1 Fisika. Fokus pada latihan soal UTBK dan cara membaca soal cerita fisika.',
                'pengalaman' => 'Pengajar internal EduLink sejak 2023, sebelumnya empat tahun di bimbel persiapan PTN.',
                'jadwal' => [['selasa', '19:00'], ['kamis', '19:00'], ['minggu', '10:00']],
            ],
            [
                'nama' => 'Sari Wulandari', 'status' => StatusTutor::Internal, 'kontak' => 'sari.wulandari@edulink.test', 'no_hp' => '081266100203',
                'verifikasi' => StatusVerifikasiTutor::Terverifikasi, 'akun' => false, 'dokumen' => null,
                'mapel' => ['CAL-SD', 'BIN-SD'], 'tarif' => 60000, 'lokasi' => 'Jelutung', 'online' => false, 'kunjungan' => true,
                'pendidikan' => ['pendidikan' => 'S1', 'institusi' => 'Universitas Jambi', 'jurusan' => 'PGSD'],
                'kompetensi' => 'S1 PGSD. Mengajar membaca, menulis, dan berhitung untuk kelas 1 sampai 3.',
                'pengalaman' => 'Delapan tahun guru kelas rendah, sekarang khusus les calistung di rumah.',
                'jadwal' => [['senin', '15:00'], ['kamis', '15:00']],
            ],
            [
                'nama' => 'Andi Pratama', 'status' => StatusTutor::Eksternal, 'kontak' => 'andi.pratama@edulink.test', 'no_hp' => '081266100204',
                'verifikasi' => StatusVerifikasiTutor::Terverifikasi, 'akun' => true, 'dokumen' => StatusVerifikasi::Valid,
                'mapel' => ['BIG-SMP', 'BIG-SMA'], 'tarif' => 90000, 'lokasi' => 'Kota Baru', 'online' => true, 'kunjungan' => false,
                'pendidikan' => ['pendidikan' => 'S1', 'institusi' => 'UIN Sulthan Thaha Saifuddin Jambi', 'jurusan' => 'Sastra Inggris'],
                'kompetensi' => 'S1 Sastra Inggris, sertifikat TOEFL ITP 610. Latihan percakapan dan tata bahasa untuk ujian sekolah.',
                'pengalaman' => 'Lima tahun mengajar daring untuk siswa SMP dan SMA di seluruh Kota Jambi.',
                'jadwal' => [['selasa', '16:00'], ['kamis', '19:00'], ['jumat', '19:00'], ['sabtu', '10:00']],
            ],
            [
                'nama' => 'Yusuf Hidayat', 'status' => StatusTutor::Internal, 'kontak' => 'yusuf.hidayat@edulink.test', 'no_hp' => '081266100205',
                'verifikasi' => StatusVerifikasiTutor::Terverifikasi, 'akun' => false, 'dokumen' => null,
                'mapel' => ['MTK-SMA', 'KIM-SMA'], 'tarif' => 100000, 'lokasi' => 'Paal Merah', 'online' => true, 'kunjungan' => true,
                'pendidikan' => ['pendidikan' => 'S1', 'institusi' => 'Universitas Jambi', 'jurusan' => 'Pendidikan Kimia'],
                'kompetensi' => 'S1 Pendidikan Kimia. Matematika SMA untuk kelas 10 sampai 12, termasuk kalkulus dasar.',
                'pengalaman' => 'Tujuh tahun mengajar di sekolah swasta, bergabung dengan EduLink tahun ini.',
                'jadwal' => [['rabu', '19:00'], ['sabtu', '13:00']],
            ],
            [
                'nama' => 'Dewi Anggraini', 'status' => StatusTutor::Eksternal, 'kontak' => 'dewi.anggraini@edulink.test', 'no_hp' => '081266100206',
                'verifikasi' => StatusVerifikasiTutor::Terverifikasi, 'akun' => true, 'dokumen' => StatusVerifikasi::Valid,
                'mapel' => ['BIO-SMA', 'IPA-SMP'], 'tarif' => 85000, 'lokasi' => 'Kota Baru', 'online' => true, 'kunjungan' => true,
                'pendidikan' => ['pendidikan' => 'S1', 'institusi' => 'Universitas Jambi', 'jurusan' => 'Biologi'],
                'kompetensi' => 'S1 Biologi. Menyiapkan siswa untuk ulangan harian dan olimpiade sains tingkat kota.',
                'pengalaman' => 'Empat tahun les privat dan pembina ekskul KIR.',
                'jadwal' => [['senin', '19:00'], ['jumat', '16:00']],
            ],
            [
                'nama' => 'Maya Handayani', 'status' => StatusTutor::Internal, 'kontak' => 'maya.handayani@edulink.test', 'no_hp' => '081266100207',
                'verifikasi' => StatusVerifikasiTutor::Terverifikasi, 'akun' => false, 'dokumen' => null,
                'mapel' => ['IPS-SD', 'IPS-SMP', 'BIN-SMP'], 'tarif' => 65000, 'lokasi' => 'Jambi Selatan', 'online' => true, 'kunjungan' => true,
                'pendidikan' => ['pendidikan' => 'S1', 'institusi' => 'Universitas Jambi', 'jurusan' => 'Pendidikan IPS'],
                'kompetensi' => 'S1 Pendidikan IPS. Membuat ringkasan materi bergambar untuk anak yang sulit menghafal.',
                'pengalaman' => 'Lima tahun guru IPS SMP.',
                'jadwal' => [['selasa', '15:00'], ['sabtu', '10:00']],
            ],
            [
                'nama' => 'Hendra Wijaya', 'status' => StatusTutor::Internal, 'kontak' => 'hendra.wijaya@edulink.test', 'no_hp' => '081266100208',
                'verifikasi' => StatusVerifikasiTutor::Terverifikasi, 'akun' => false, 'dokumen' => null,
                'mapel' => ['MTK-SD', 'CAL-SD'], 'tarif' => 50000, 'lokasi' => 'Danau Sipin', 'online' => false, 'kunjungan' => true,
                'pendidikan' => ['pendidikan' => 'D3', 'institusi' => 'Politeknik Jambi', 'jurusan' => 'Matematika'],
                'kompetensi' => 'D3 Matematika. Sabar menghadapi anak yang takut berhitung.',
                'pengalaman' => 'Tiga tahun les privat SD di Danau Sipin dan Pelayangan.',
                'jadwal' => [['rabu', '15:00'], ['jumat', '15:00']],
            ],
            [
                'nama' => 'Nurul Aisyah', 'status' => StatusTutor::Eksternal, 'kontak' => 'nurul.aisyah@edulink.test', 'no_hp' => '081266100209',
                'verifikasi' => StatusVerifikasiTutor::Menunggu, 'akun' => true, 'dokumen' => StatusVerifikasi::Menunggu, 'diajukan_hari_lalu' => 3,
                'mapel' => ['BIG-SD', 'BIN-SD'], 'tarif' => 55000, 'lokasi' => 'Pasar Jambi', 'online' => true, 'kunjungan' => true,
                'pendidikan' => ['pendidikan' => 'S1', 'institusi' => 'Universitas Jambi', 'jurusan' => 'Pendidikan Bahasa Inggris'],
                'kompetensi' => 'S1 Pendidikan Bahasa Inggris. Lagu dan permainan untuk Bahasa Inggris anak SD.',
                'pengalaman' => 'Dua tahun asisten guru di kursus bahasa.',
                'jadwal' => [['kamis', '16:00'], ['sabtu', '08:00']],
            ],
            [
                'nama' => 'Bayu Firmansyah', 'status' => StatusTutor::Eksternal, 'kontak' => 'bayu.firmansyah@edulink.test', 'no_hp' => '081266100210',
                'verifikasi' => StatusVerifikasiTutor::Menunggu, 'akun' => true, 'dokumen' => StatusVerifikasi::Menunggu, 'diajukan_hari_lalu' => 1,
                'mapel' => ['MTK-SMA', 'FIS-SMA'], 'tarif' => 120000, 'lokasi' => 'Telanaipura', 'online' => true, 'kunjungan' => false,
                'pendidikan' => ['pendidikan' => 'S2', 'institusi' => 'Institut Teknologi Bandung', 'jurusan' => 'Fisika'],
                'kompetensi' => 'S2 Fisika. Pembahasan soal olimpiade dan UTBK saintek.',
                'pengalaman' => 'Tiga tahun dosen luar biasa, mengajar privat secara daring.',
                'jadwal' => [['senin', '20:00'], ['rabu', '20:00']],
            ],
        ];
    }

    public function run(): void
    {
        $admin = AkunPengguna::query()->where('peran', Peran::Admin)->orderBy('id_akun')->firstOrFail();

        foreach ($this->daftarTutor() as $data) {
            $tutor = $this->buatTutor($data);

            $tutor->profil()->create([
                'riwayat_pendidikan' => $data['pendidikan'],
                'kompetensi' => $data['kompetensi'],
                'pengalaman' => $data['pengalaman'],
                'tarif' => $data['tarif'],
                'lokasi' => $data['lokasi'],
                'melayani_online' => $data['online'],
                'melayani_kunjungan' => $data['kunjungan'],
            ]);

            $tutor->mataPelajaran()->attach($data['mapel']);
            $this->buatJadwal($tutor, $data['jadwal']);

            if ($data['dokumen'] !== null) {
                foreach (JenisDokumen::cases() as $jenis) {
                    $this->buatDokumen($tutor, $jenis, $data['dokumen'], $data['dokumen'] === StatusVerifikasi::Valid ? now()->subMonths(2) : null);
                }
            }

            if ($data['verifikasi'] === StatusVerifikasiTutor::Terverifikasi) {
                $tutor->riwayatVerifikasi()->create(['status' => StatusVerifikasiTutor::Terverifikasi, 'id_akun' => $admin->id_akun, 'dicatat_pada' => now()->subMonths(2)]);
            }

            if ($data['verifikasi'] === StatusVerifikasiTutor::Menunggu) {
                $this->catatPengajuan($tutor, now()->subDays($data['diajukan_hari_lalu'] ?? 1));
            }
        }

        $this->isiRekening();
        $this->tutorInternalBelumAktif();
        $this->pendaftarPerluPerbaikan($admin);
        $this->pendaftarDraf();
    }

    /**
     * Payout accounts for the verified external tutors who sign in for the demo.
     */
    private function isiRekening(): void
    {
        $rekening = [
            'rina.marlina@edulink.test' => ['BRI', '0123456789014417', 'Rina Marlina'],
            'andi.pratama@edulink.test' => ['BNI', '0987654321', 'Andi Pratama'],
            'dewi.anggraini@edulink.test' => ['Bank Jambi', '2001300456', 'Dewi Anggraini'],
            'yusuf.hidayat@edulink.test' => ['BSI', '7001234567', 'Yusuf Hidayat'],
        ];

        foreach ($rekening as $kontak => [$bank, $nomor, $atasNama]) {
            Tutor::query()->where('kontak', $kontak)->firstOrFail()
                ->forceFill(['rekening_bank' => $bank, 'rekening_nomor' => $nomor, 'rekening_atas_nama' => $atasNama])
                ->save();
        }
    }

    /**
     * Two tutors already teaching at the bimbel, imported from the company's
     * data. No account yet; rate, district and schedule are still missing.
     * They activate at /aktivasi-tutor with their email.
     */
    private function tutorInternalBelumAktif(): void
    {
        $data = [
            [
                'nama' => 'Lestari Putri', 'kontak' => 'lestari.putri@edulink.test', 'no_hp' => '081266100211', 'mapel' => ['MTK-SMP', 'IPA-SMP'],
                'pendidikan' => ['pendidikan' => 'S1', 'institusi' => 'Universitas Jambi', 'jurusan' => 'Pendidikan Matematika'],
                'kompetensi' => 'S1 Pendidikan Matematika. Pengajar kelas reguler SMP di bimbel EduLink.',
                'pengalaman' => 'Empat tahun mengajar kelas reguler bimbel EduLink cabang Telanaipura.',
            ],
            [
                'nama' => 'Rahmat Saputra', 'kontak' => 'rahmat.saputra@edulink.test', 'no_hp' => '081266100212', 'mapel' => ['BIN-SMA', 'BIG-SMA'],
                'pendidikan' => ['pendidikan' => 'S1', 'institusi' => 'Universitas Jambi', 'jurusan' => 'Pendidikan Bahasa Indonesia'],
                'kompetensi' => 'S1 Pendidikan Bahasa Indonesia. Pengajar kelas persiapan ujian sekolah SMA.',
                'pengalaman' => 'Tiga tahun mengajar kelas persiapan ujian di bimbel EduLink.',
            ],
        ];

        foreach ($data as $baris) {
            $tutor = $this->buatTutor(['status' => StatusTutor::Internal, 'verifikasi' => StatusVerifikasiTutor::Draf, 'akun' => false] + $baris);

            $tutor->profil()->create([
                'riwayat_pendidikan' => $baris['pendidikan'],
                'kompetensi' => $baris['kompetensi'],
                'pengalaman' => $baris['pengalaman'],
            ]);
            $tutor->mataPelajaran()->attach($baris['mapel']);
        }
    }

    /**
     * An external application the admin sent back: the KTP photo is unreadable
     * and the teaching experience is too thin.
     */
    private function pendaftarPerluPerbaikan(AkunPengguna $admin): void
    {
        $catatan = 'Terima kasih sudah mendaftar. Dua hal perlu diperbaiki sebelum kami bisa menyetujui.';
        $bagian = [
            'dokumen' => 'Foto KTP buram, NIK tidak terbaca. Unggah ulang foto yang jelas dan tidak terpotong.',
            'profil' => 'Tuliskan pengalaman mengajar lebih rinci: berapa tahun, jenjang apa, dan di mana.',
        ];

        $tutor = $this->buatTutor([
            'nama' => 'Intan Permata', 'status' => StatusTutor::Eksternal, 'kontak' => 'intan.permata@edulink.test', 'no_hp' => '081266100213',
            'verifikasi' => StatusVerifikasiTutor::PerluPerbaikan, 'akun' => true,
        ]);
        $tutor->forceFill(['catatan_verifikasi' => $catatan, 'bagian_perbaikan' => $bagian, 'diajukan_pada' => now()->subDays(5)])->save();

        $tutor->profil()->create([
            'riwayat_pendidikan' => ['pendidikan' => 'S1', 'institusi' => 'Universitas Jambi', 'jurusan' => 'Kimia'],
            'kompetensi' => 'S1 Kimia. Stoikiometri dan reaksi redoks untuk SMA.',
            'pengalaman' => 'Pernah mengajar.',
            'tarif' => 80000,
            'lokasi' => 'Kota Baru',
            'melayani_online' => true,
            'melayani_kunjungan' => true,
        ]);
        $tutor->mataPelajaran()->attach(['KIM-SMA', 'IPA-SMP']);
        $this->buatJadwal($tutor, [['selasa', '19:00'], ['kamis', '16:00']]);

        $this->buatDokumen($tutor, JenisDokumen::Ijazah, StatusVerifikasi::Valid, now()->subDays(2));
        $ktp = $this->buatDokumen($tutor, JenisDokumen::Ktp, StatusVerifikasi::Ditolak, now()->subDays(2));
        $ktp->forceFill(['alasan_penolakan' => $bagian['dokumen']])->save();

        $this->catatPengajuan($tutor, now()->subDays(5));
        $tutor->riwayatVerifikasi()->create([
            'status' => StatusVerifikasiTutor::PerluPerbaikan, 'catatan' => $catatan, 'bagian' => $bagian,
            'id_akun' => $admin->id_akun, 'dicatat_pada' => now()->subDays(2),
        ]);
    }

    /**
     * An external tutor who saved the first two steps and has not uploaded
     * documents or a schedule yet.
     */
    private function pendaftarDraf(): void
    {
        $tutor = $this->buatTutor([
            'nama' => 'Galih Prakoso', 'status' => StatusTutor::Eksternal, 'kontak' => 'galih.prakoso@edulink.test', 'no_hp' => '081266100214',
            'verifikasi' => StatusVerifikasiTutor::Draf, 'akun' => true,
        ]);

        $tutor->profil()->create([
            'riwayat_pendidikan' => ['pendidikan' => 'S1', 'institusi' => 'Universitas Batanghari', 'jurusan' => 'Teknik Sipil'],
            'kompetensi' => 'Juara olimpiade matematika tingkat provinsi. Suka menjelaskan dengan gambar.',
            'pengalaman' => 'Dua tahun les privat matematika SD dan SMP untuk anak tetangga.',
            'tarif' => 70000,
            'lokasi' => 'Jambi Timur',
            'melayani_online' => true,
            'melayani_kunjungan' => true,
        ]);
        $tutor->mataPelajaran()->attach(['MTK-SD', 'MTK-SMP']);
    }

    /**
     * @param  array{nama: string, status: StatusTutor, kontak: string, no_hp: string, verifikasi: StatusVerifikasiTutor, akun: bool}  $data
     */
    private function buatTutor(array $data): Tutor
    {
        $akun = null;

        if ($data['akun']) {
            $akun = AkunPengguna::forceCreate([
                'nama' => $data['nama'],
                'kontak' => $data['kontak'],
                'no_hp' => $data['no_hp'],
                'peran' => Peran::Tutor,
                'kredensial' => self::KATA_SANDI,
                'persetujuan_pada' => now(),
                'versi_kebijakan' => config('edulink.versi_kebijakan'),
            ]);
        }

        $tutor = new Tutor([
            'id_akun' => $akun?->id_akun,
            'nama' => $data['nama'],
            'status' => $data['status'],
            'kontak' => $data['kontak'],
            'no_hp' => $data['no_hp'],
        ]);
        $tutor->status_verifikasi = $data['verifikasi'];
        $tutor->save();

        return $tutor;
    }

    /**
     * @param  list<array{0: string, 1: string}>  $jadwal
     */
    private function buatJadwal(Tutor $tutor, array $jadwal): void
    {
        foreach ($jadwal as [$hari, $jam]) {
            $tutor->jadwal()->create(['hari' => $hari, 'jam' => $jam.':00', 'status' => 'tersedia']);
        }
    }

    private function buatDokumen(Tutor $tutor, JenisDokumen $jenis, StatusVerifikasi $status, ?DateTimeInterface $diperiksa): DokumenVerifikasi
    {
        return $tutor->dokumen()->create([
            'jenis' => $jenis,
            'berkas' => BerkasContoh::simpan($tutor->id_tutor, $jenis->label().' - '.$tutor->nama),
            'status_verifikasi' => $status,
            'diperiksa_pada' => $diperiksa,
        ]);
    }

    private function catatPengajuan(Tutor $tutor, DateTimeInterface $pada): void
    {
        $tutor->forceFill(['diajukan_pada' => $pada])->save();
        $tutor->riwayatVerifikasi()->create(['status' => StatusVerifikasiTutor::Menunggu, 'id_akun' => $tutor->id_akun, 'dicatat_pada' => $pada]);
    }
}
