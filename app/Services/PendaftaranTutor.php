<?php

namespace App\Services;

use App\Enums\JenisDokumen;
use App\Enums\LangkahPendaftaran;
use App\Enums\Peran;
use App\Enums\StatusTutor;
use App\Enums\StatusVerifikasi;
use App\Enums\StatusVerifikasiTutor;
use App\Exceptions\AksiDitolak;
use App\Models\AkunPengguna;
use App\Models\DokumenVerifikasi;
use App\Models\Pesanan;
use App\Models\Tutor;
use App\Support\BerkasContoh;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * The tutor's side of an application: each step of the form is saved as a
 * draft, the completeness check, and sending it to the admin. Every write
 * refuses once the application is no longer draf or perlu_perbaikan.
 */
class PendaftaranTutor
{
    public const SERTIFIKAT_MAKS = 5;

    /**
     * Folder on the public disk for profile photos.
     */
    public const FOLDER_FOTO = 'foto-tutor';

    /**
     * Step 1 for a new external tutor: the account and a draft tutor record.
     *
     * @param  array{nama: string, kontak: string, no_hp: string, password: string}  $data
     */
    public function daftar(array $data): Tutor
    {
        return DB::transaction(function () use ($data): Tutor {
            $akun = new AkunPengguna(['nama' => $data['nama'], 'kontak' => $data['kontak'], 'kredensial' => $data['password']]);
            $akun->peran = Peran::Tutor;
            $akun->save();

            $tutor = new Tutor([
                'id_akun' => $akun->id_akun,
                'nama' => $data['nama'],
                'status' => StatusTutor::Eksternal,
                'kontak' => $data['kontak'],
                'no_hp' => $data['no_hp'],
            ]);
            $tutor->status_verifikasi = StatusVerifikasiTutor::Draf;
            $tutor->save();

            return $tutor;
        });
    }

    /**
     * @param  array{nama: string, no_hp: string}  $data
     */
    public function simpanDataDiri(Tutor $tutor, array $data): void
    {
        $this->pastikanBisaDiubah($tutor);

        DB::transaction(function () use ($tutor, $data): void {
            $tutor->fill(['nama' => $data['nama'], 'no_hp' => $data['no_hp']]);
            $this->tandaiDiperbaiki($tutor, LangkahPendaftaran::DataDiri);
            $tutor->save();

            $tutor->akun?->update(['nama' => $data['nama']]);
        });
    }

    /**
     * @param  array{pendidikan: string, institusi: string, jurusan: string, kompetensi: string, pengalaman: string, mapel: list<string>, tarif: int|string, lokasi: string, layanan: list<string>}  $data
     */
    public function simpanProfil(Tutor $tutor, array $data, ?UploadedFile $foto = null): void
    {
        $this->pastikanBisaDiubah($tutor);
        $fotoLama = null;

        DB::transaction(function () use ($tutor, $data, $foto, &$fotoLama): void {
            $profil = $tutor->profil()->firstOrNew();
            $profil->fill([
                'riwayat_pendidikan' => ['pendidikan' => $data['pendidikan'], 'institusi' => $data['institusi'], 'jurusan' => $data['jurusan']],
                'kompetensi' => $data['kompetensi'],
                'pengalaman' => $data['pengalaman'],
                'tarif' => $data['tarif'],
                'lokasi' => $data['lokasi'],
                'melayani_online' => in_array('online', $data['layanan'], true),
                'melayani_kunjungan' => in_array('kunjungan_rumah', $data['layanan'], true),
            ]);

            if ($foto !== null) {
                $fotoLama = $profil->foto;
                $profil->foto = $foto->store(self::FOLDER_FOTO, 'public');
            }

            $tutor->profil()->save($profil);
            $tutor->mataPelajaran()->sync($data['mapel']);

            $this->tandaiDiperbaiki($tutor, LangkahPendaftaran::Profil);
            $tutor->save();
        });

        if ($fotoLama !== null) {
            Storage::disk('public')->delete($fotoLama);
        }
    }

    /**
     * Ijazah and KTP are one per tutor, so a new upload replaces the old file.
     * Certificates are added, up to SERTIFIKAT_MAKS.
     */
    public function unggahDokumen(Tutor $tutor, JenisDokumen $jenis, UploadedFile $berkas): DokumenVerifikasi
    {
        $this->pastikanBisaDiubah($tutor);

        $lama = $jenis === JenisDokumen::Sertifikat ? collect() : $tutor->dokumen()->where('jenis', $jenis)->get();

        if ($jenis === JenisDokumen::Sertifikat && $tutor->dokumen()->where('jenis', $jenis)->count() >= self::SERTIFIKAT_MAKS) {
            throw new AksiDitolak('Sertifikat paling banyak '.self::SERTIFIKAT_MAKS.' berkas. Hapus salah satu sebelum mengunggah yang baru.');
        }

        $path = $berkas->store(BerkasContoh::FOLDER.'/'.$tutor->id_tutor, 'local');

        $dokumen = DB::transaction(function () use ($tutor, $jenis, $path, $lama): DokumenVerifikasi {
            $lama->each->delete();

            $dokumen = $tutor->dokumen()->create(['jenis' => $jenis, 'berkas' => $path, 'status_verifikasi' => StatusVerifikasi::Menunggu]);

            $this->tandaiDiperbaiki($tutor, LangkahPendaftaran::Dokumen);
            $tutor->save();

            return $dokumen;
        });

        Storage::disk('local')->delete($lama->pluck('berkas')->all());

        return $dokumen;
    }

    public function hapusDokumen(Tutor $tutor, DokumenVerifikasi $dokumen): void
    {
        $this->pastikanBisaDiubah($tutor);

        $dokumen->delete();
        Storage::disk('local')->delete($dokumen->berkas);
    }

    /**
     * Replaces the weekly slots. A slot an order already points at is kept.
     *
     * @param  list<array{hari: string, jam: string}>  $slot
     */
    public function simpanJadwal(Tutor $tutor, array $slot): void
    {
        $this->pastikanBisaDiubah($tutor);

        DB::transaction(function () use ($tutor, $slot): void {
            $tutor->jadwal()->whereNotIn('id_jadwal', Pesanan::query()->select('id_jadwal'))->delete();

            $tersisa = $tutor->jadwal()->get()->map(fn ($jadwal): string => $jadwal->hari->value.' '.substr($jadwal->jam, 0, 5));

            foreach ($slot as $baris) {
                if (! $tersisa->contains($baris['hari'].' '.$baris['jam'])) {
                    $tutor->jadwal()->create(['hari' => $baris['hari'], 'jam' => $baris['jam'].':00', 'status' => 'tersedia']);
                }
            }

            $this->tandaiDiperbaiki($tutor, LangkahPendaftaran::Jadwal);
            $tutor->save();
        });
    }

    /**
     * What is still missing, per step. An empty array means the application can be sent.
     *
     * @return array<string, list<string>>
     */
    public function kekurangan(Tutor $tutor): array
    {
        $tutor->loadMissing(['profil', 'mataPelajaran', 'dokumen', 'jadwal']);
        $kurang = [];

        if (blank($tutor->nama) || blank($tutor->no_hp)) {
            $kurang[LangkahPendaftaran::DataDiri->value][] = 'Nama dan nomor HP wajib diisi.';
        }

        $profil = $tutor->profil;

        if ($profil === null) {
            $kurang[LangkahPendaftaran::Profil->value][] = 'Profil mengajar belum diisi.';
        } else {
            if (blank($profil->riwayat_pendidikan['pendidikan'] ?? null) || blank($profil->riwayat_pendidikan['institusi'] ?? null)) {
                $kurang[LangkahPendaftaran::Profil->value][] = 'Riwayat pendidikan belum lengkap.';
            }
            if (blank($profil->kompetensi) || blank($profil->pengalaman)) {
                $kurang[LangkahPendaftaran::Profil->value][] = 'Kompetensi dan pengalaman mengajar wajib diisi.';
            }
            if ($profil->tarif === null) {
                $kurang[LangkahPendaftaran::Profil->value][] = 'Tarif per jam belum diisi.';
            }
            if (blank($profil->lokasi)) {
                $kurang[LangkahPendaftaran::Profil->value][] = 'Kecamatan layanan belum dipilih.';
            }
            if ($profil->modes() === []) {
                $kurang[LangkahPendaftaran::Profil->value][] = 'Pilih minimal satu layanan: online atau kunjungan rumah.';
            }
        }

        if ($tutor->mataPelajaran->isEmpty()) {
            $kurang[LangkahPendaftaran::Profil->value][] = 'Pilih minimal satu mata pelajaran.';
        }

        if (! $tutor->isInternal()) {
            foreach ([JenisDokumen::Ijazah, JenisDokumen::Ktp] as $jenis) {
                if (! $tutor->dokumen->contains('jenis', $jenis)) {
                    $kurang[LangkahPendaftaran::Dokumen->value][] = $jenis->label().' wajib diunggah.';
                }
            }
        }

        foreach ($tutor->dokumen->where('status_verifikasi', StatusVerifikasi::Ditolak) as $dokumen) {
            $kurang[LangkahPendaftaran::Dokumen->value][] = $dokumen->jenis->label().' ditolak tim akademik. Unggah ulang atau hapus berkas itu.';
        }

        if ($tutor->jadwal->isEmpty()) {
            $kurang[LangkahPendaftaran::Jadwal->value][] = 'Tambahkan minimal satu jadwal mingguan.';
        }

        foreach (LangkahPendaftaran::bagian() as $langkah) {
            if ($tutor->perluDiperbaiki($langkah)) {
                $kurang[$langkah->value][] = 'Belum diperbaiki sesuai catatan tim akademik. Buka langkah ini, perbaiki, lalu simpan.';
            }
        }

        // Keep the steps in form order.
        return collect(LangkahPendaftaran::bagian())
            ->mapWithKeys(fn (LangkahPendaftaran $langkah): array => [$langkah->value => $kurang[$langkah->value] ?? []])
            ->filter()
            ->all();
    }

    /**
     * Where an editable application should open: the first flagged section,
     * otherwise the first incomplete one, otherwise the summary.
     */
    public function langkahBerikutnya(Tutor $tutor): LangkahPendaftaran
    {
        foreach (LangkahPendaftaran::bagian() as $langkah) {
            if ($tutor->perluDiperbaiki($langkah)) {
                return $langkah;
            }
        }

        $kurang = array_key_first($this->kekurangan($tutor));

        return $kurang === null ? LangkahPendaftaran::Kirim : LangkahPendaftaran::from($kurang);
    }

    /**
     * Sends the application to the admin queue.
     */
    public function kirim(Tutor $tutor): void
    {
        DB::transaction(function () use ($tutor): void {
            $tutor = Tutor::query()->lockForUpdate()->findOrFail($tutor->id_tutor);

            $this->pastikanBisaDiubah($tutor);

            if ($this->kekurangan($tutor) !== []) {
                throw new AksiDitolak('Pendaftaran belum lengkap. Lengkapi bagian yang ditandai, lalu kirim lagi.');
            }

            $tutor->status_verifikasi = StatusVerifikasiTutor::Menunggu;
            $tutor->diajukan_pada = now();
            $tutor->save();

            $tutor->riwayatVerifikasi()->create(['status' => StatusVerifikasiTutor::Menunggu, 'id_akun' => $tutor->id_akun, 'dicatat_pada' => now()]);
        });

        $tutor->refresh();
    }

    /**
     * The page a tutor account belongs on right now, by application status.
     */
    public function tujuan(Tutor $tutor): string
    {
        return match ($tutor->status_verifikasi) {
            StatusVerifikasiTutor::Draf, StatusVerifikasiTutor::PerluPerbaikan => route('tutor.pendaftaran.langkah', $this->langkahBerikutnya($tutor)),
            StatusVerifikasiTutor::Menunggu, StatusVerifikasiTutor::Ditolak => route('tutor.status'),
            StatusVerifikasiTutor::Terverifikasi => route('tutor.area'),
        };
    }

    private function pastikanBisaDiubah(Tutor $tutor): void
    {
        if (! $tutor->status_verifikasi->bisaDiubah()) {
            throw new AksiDitolak('Pendaftaran sudah dikirim dan sedang diperiksa, jadi belum bisa diubah.');
        }
    }

    /**
     * Saving a flagged section counts as the tutor's correction of it.
     */
    private function tandaiDiperbaiki(Tutor $tutor, LangkahPendaftaran $langkah): void
    {
        $bagian = $tutor->bagian_perbaikan ?? [];
        unset($bagian[$langkah->value]);

        $tutor->bagian_perbaikan = $bagian === [] ? null : $bagian;
    }
}
