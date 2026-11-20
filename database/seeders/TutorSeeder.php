<?php

namespace Database\Seeders;

use App\Enums\JenisDokumen;
use App\Enums\StatusTutor;
use App\Enums\StatusVerifikasi;
use App\Models\Tutor;
use Illuminate\Database\Seeder;

/**
 * Eight listed tutors (the first four are the sample cards on the landing
 * page) and two external tutors whose documents wait for the admin.
 */
class TutorSeeder extends Seeder
{
    /**
     * @var list<array{nama: string, status: StatusTutor, kontak: string, dokumen: StatusVerifikasi|null, mapel: list<string>, tarif: int, lokasi: string, online: bool, kunjungan: bool, kompetensi: string, pengalaman: string, jadwal: list<array{0: string, 1: string}>}>
     */
    private function daftarTutor(): array
    {
        return [
            [
                'nama' => 'Rina Marlina', 'status' => StatusTutor::Eksternal, 'kontak' => '081266100201', 'dokumen' => StatusVerifikasi::Valid,
                'mapel' => ['MTK-SD', 'MTK-SMP', 'IPA-SD', 'IPA-SMP'], 'tarif' => 75000, 'lokasi' => 'Telanaipura', 'online' => true, 'kunjungan' => true,
                'kompetensi' => 'S1 Pendidikan Matematika. Terbiasa mengajar anak yang tertinggal di pecahan dan aljabar dasar dengan benda sehari-hari.',
                'pengalaman' => 'Enam tahun les privat SD dan SMP di Telanaipura dan Kota Baru.',
                'jadwal' => [['senin', '16:00'], ['rabu', '16:00'], ['sabtu', '09:00']],
            ],
            [
                'nama' => 'Fadli Ramadhan', 'status' => StatusTutor::Internal, 'kontak' => '081266100202', 'dokumen' => null,
                'mapel' => ['FIS-SMA', 'UTBK-SMA'], 'tarif' => 110000, 'lokasi' => 'Alam Barajo', 'online' => true, 'kunjungan' => true,
                'kompetensi' => 'S1 Fisika. Fokus pada latihan soal UTBK dan cara membaca soal cerita fisika.',
                'pengalaman' => 'Pengajar internal EduLink sejak 2023, sebelumnya empat tahun di bimbel persiapan PTN.',
                'jadwal' => [['selasa', '19:00'], ['kamis', '19:00'], ['minggu', '10:00']],
            ],
            [
                'nama' => 'Sari Wulandari', 'status' => StatusTutor::Internal, 'kontak' => '081266100203', 'dokumen' => null,
                'mapel' => ['CAL-SD', 'BIN-SD'], 'tarif' => 60000, 'lokasi' => 'Jelutung', 'online' => false, 'kunjungan' => true,
                'kompetensi' => 'S1 PGSD. Mengajar membaca, menulis, dan berhitung untuk kelas 1 sampai 3.',
                'pengalaman' => 'Delapan tahun guru kelas rendah, sekarang khusus les calistung di rumah.',
                'jadwal' => [['senin', '15:00'], ['kamis', '15:00']],
            ],
            [
                'nama' => 'Andi Pratama', 'status' => StatusTutor::Eksternal, 'kontak' => '081266100204', 'dokumen' => StatusVerifikasi::Valid,
                'mapel' => ['BIG-SMP', 'BIG-SMA'], 'tarif' => 90000, 'lokasi' => 'Kota Baru', 'online' => true, 'kunjungan' => false,
                'kompetensi' => 'S1 Sastra Inggris, sertifikat TOEFL ITP 610. Latihan percakapan dan tata bahasa untuk ujian sekolah.',
                'pengalaman' => 'Lima tahun mengajar daring untuk siswa SMP dan SMA di seluruh Kota Jambi.',
                'jadwal' => [['selasa', '16:00'], ['jumat', '19:00']],
            ],
            [
                'nama' => 'Yusuf Hidayat', 'status' => StatusTutor::Internal, 'kontak' => '081266100205', 'dokumen' => null,
                'mapel' => ['MTK-SMA', 'KIM-SMA'], 'tarif' => 100000, 'lokasi' => 'Paal Merah', 'online' => true, 'kunjungan' => true,
                'kompetensi' => 'S1 Pendidikan Kimia. Matematika SMA untuk kelas 10 sampai 12, termasuk kalkulus dasar.',
                'pengalaman' => 'Tujuh tahun mengajar di sekolah swasta, bergabung dengan EduLink tahun ini.',
                'jadwal' => [['rabu', '19:00'], ['sabtu', '13:00']],
            ],
            [
                'nama' => 'Dewi Anggraini', 'status' => StatusTutor::Eksternal, 'kontak' => '081266100206', 'dokumen' => StatusVerifikasi::Valid,
                'mapel' => ['BIO-SMA', 'IPA-SMP'], 'tarif' => 85000, 'lokasi' => 'Kota Baru', 'online' => true, 'kunjungan' => true,
                'kompetensi' => 'S1 Biologi. Menyiapkan siswa untuk ulangan harian dan olimpiade sains tingkat kota.',
                'pengalaman' => 'Empat tahun les privat dan pembina ekskul KIR.',
                'jadwal' => [['senin', '19:00'], ['jumat', '16:00']],
            ],
            [
                'nama' => 'Maya Handayani', 'status' => StatusTutor::Internal, 'kontak' => '081266100207', 'dokumen' => null,
                'mapel' => ['IPS-SD', 'IPS-SMP', 'BIN-SMP'], 'tarif' => 65000, 'lokasi' => 'Jambi Selatan', 'online' => true, 'kunjungan' => true,
                'kompetensi' => 'S1 Pendidikan IPS. Membuat ringkasan materi bergambar untuk anak yang sulit menghafal.',
                'pengalaman' => 'Lima tahun guru IPS SMP.',
                'jadwal' => [['selasa', '15:00'], ['sabtu', '10:00']],
            ],
            [
                'nama' => 'Hendra Wijaya', 'status' => StatusTutor::Internal, 'kontak' => '081266100208', 'dokumen' => null,
                'mapel' => ['MTK-SD', 'CAL-SD'], 'tarif' => 50000, 'lokasi' => 'Danau Sipin', 'online' => false, 'kunjungan' => true,
                'kompetensi' => 'D3 Matematika. Sabar menghadapi anak yang takut berhitung.',
                'pengalaman' => 'Tiga tahun les privat SD di Danau Sipin dan Pelayangan.',
                'jadwal' => [['rabu', '15:00'], ['jumat', '15:00']],
            ],
            [
                'nama' => 'Nurul Aisyah', 'status' => StatusTutor::Eksternal, 'kontak' => '081266100209', 'dokumen' => StatusVerifikasi::Menunggu,
                'mapel' => ['BIG-SD', 'BIN-SD'], 'tarif' => 55000, 'lokasi' => 'Pasar Jambi', 'online' => true, 'kunjungan' => true,
                'kompetensi' => 'S1 Pendidikan Bahasa Inggris. Lagu dan permainan untuk Bahasa Inggris anak SD.',
                'pengalaman' => 'Dua tahun asisten guru di kursus bahasa.',
                'jadwal' => [['kamis', '16:00'], ['sabtu', '08:00']],
            ],
            [
                'nama' => 'Bayu Firmansyah', 'status' => StatusTutor::Eksternal, 'kontak' => '081266100210', 'dokumen' => StatusVerifikasi::Menunggu,
                'mapel' => ['MTK-SMA', 'FIS-SMA'], 'tarif' => 120000, 'lokasi' => 'Telanaipura', 'online' => true, 'kunjungan' => false,
                'kompetensi' => 'S2 Fisika. Pembahasan soal olimpiade dan UTBK saintek.',
                'pengalaman' => 'Tiga tahun dosen luar biasa, mengajar privat secara daring.',
                'jadwal' => [['senin', '20:00'], ['rabu', '20:00']],
            ],
        ];
    }

    public function run(): void
    {
        foreach ($this->daftarTutor() as $data) {
            $tutor = Tutor::query()->create([
                'nama' => $data['nama'],
                'status' => $data['status'],
                'kontak' => $data['kontak'],
            ]);

            $tutor->profil()->create([
                'kompetensi' => $data['kompetensi'],
                'pengalaman' => $data['pengalaman'],
                'tarif' => $data['tarif'],
                'lokasi' => $data['lokasi'],
                'melayani_online' => $data['online'],
                'melayani_kunjungan' => $data['kunjungan'],
            ]);

            $tutor->mataPelajaran()->attach($data['mapel']);

            foreach ($data['jadwal'] as [$hari, $jam]) {
                $tutor->jadwal()->create(['hari' => $hari, 'jam' => $jam.':00', 'status' => 'tersedia']);
            }

            if ($data['dokumen'] !== null) {
                foreach (JenisDokumen::cases() as $jenis) {
                    $tutor->dokumen()->create([
                        'jenis' => $jenis,
                        // Placeholder path; real uploads are not built yet.
                        'berkas' => 'dokumen-contoh/'.$tutor->id_tutor.'-'.$jenis->value.'.pdf',
                        'status_verifikasi' => $data['dokumen'],
                        'diperiksa_pada' => $data['dokumen'] === StatusVerifikasi::Valid ? now()->subMonths(2) : null,
                    ]);
                }
            }
        }
    }
}
