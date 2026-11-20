<?php

namespace Tests\Concerns;

use App\Enums\JenisDokumen;
use App\Enums\StatusTutor;
use App\Enums\StatusVerifikasi;
use App\Models\DokumenVerifikasi;
use App\Models\JadwalKetersediaan;
use App\Models\MataPelajaran;
use App\Models\ProfilTutor;
use App\Models\Tutor;
use App\Support\BerkasContoh;
use Illuminate\Http\UploadedFile;

/**
 * Tutor applications in a given state. Call Storage::fake('local') first:
 * documents are written to the private disk.
 */
trait MembuatPendaftar
{
    /**
     * A draft with every section filled in, ready to send.
     *
     * @param  array<string, mixed>  $profil
     */
    protected function pendaftarLengkap(array $profil = [], StatusTutor $status = StatusTutor::Eksternal, bool $denganDokumen = true): Tutor
    {
        $tutor = Tutor::factory()->draf()->denganAkun()
            ->has(ProfilTutor::factory()->state($profil), 'profil')
            ->has(MataPelajaran::factory()->state(['jenjang' => 'SMP']), 'mataPelajaran')
            ->has(JadwalKetersediaan::factory(), 'jadwal')
            ->create(['status' => $status]);

        if ($denganDokumen) {
            foreach ([JenisDokumen::Ijazah, JenisDokumen::Ktp] as $jenis) {
                $this->dokumenUntuk($tutor, $jenis);
            }
        }

        return $tutor->load('akun');
    }

    protected function dokumenUntuk(Tutor $tutor, JenisDokumen $jenis, StatusVerifikasi $status = StatusVerifikasi::Menunggu): DokumenVerifikasi
    {
        return $tutor->dokumen()->create([
            'jenis' => $jenis,
            'berkas' => BerkasContoh::simpan($tutor->id_tutor, $jenis->label()),
            'status_verifikasi' => $status,
        ]);
    }

    /**
     * A real PDF file, so the content-based type check passes.
     */
    protected function pdf(string $nama = 'ijazah.pdf'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($path, BerkasContoh::pdf('Berkas uji'));

        return new UploadedFile($path, $nama, null, null, true);
    }

    /**
     * Valid input for the "Profil mengajar" step.
     *
     * @param  array<string, mixed>  $ubah
     * @return array<string, mixed>
     */
    protected function isianProfil(array $ubah = []): array
    {
        MataPelajaran::query()->firstOrCreate(['kode_mapel' => 'MTK-SMP'], ['nama' => 'Matematika', 'jenjang' => 'SMP']);

        return array_merge([
            'pendidikan' => 'S1',
            'institusi' => 'Universitas Jambi',
            'jurusan' => 'Pendidikan Matematika',
            'kompetensi' => 'Mengajar aljabar dasar dengan contoh sehari-hari.',
            'pengalaman' => 'Tiga tahun les privat SMP di Telanaipura.',
            'jenjang' => ['SMP'],
            'mapel' => ['MTK-SMP'],
            'tarif' => '75.000',
            'lokasi' => 'Telanaipura',
            'layanan' => ['online', 'kunjungan_rumah'],
        ], $ubah);
    }
}
