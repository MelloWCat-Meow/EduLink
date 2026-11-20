<?php

namespace App\Services;

use App\Enums\LangkahPendaftaran;
use App\Enums\StatusVerifikasi;
use App\Enums\StatusVerifikasiTutor;
use App\Exceptions\AksiDitolak;
use App\Jobs\SinkronTutorKeEduManage;
use App\Mail\KeputusanVerifikasiTutor;
use App\Models\AkunPengguna;
use App\Models\Tutor;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * The admin's three decisions on an application that is waiting (menunggu).
 * Each one is recorded in riwayat_verifikasi_tutor (who, when, note) and
 * emailed to the tutor. Approval also hands the tutor to EduManage.
 */
class VerifikasiTutor
{
    /**
     * Every document becomes valid and the profile is listed in search.
     */
    public function setujui(Tutor $tutor, AkunPengguna $admin, ?string $catatan = null): Tutor
    {
        $tutor = $this->putuskan($tutor, $admin, StatusVerifikasiTutor::Terverifikasi, $catatan, [], function (Tutor $tutor): void {
            $tutor->dokumen()->where('status_verifikasi', '!=', StatusVerifikasi::Valid)
                ->update(['status_verifikasi' => StatusVerifikasi::Valid, 'alasan_penolakan' => null, 'diperiksa_pada' => now(), 'updated_at' => now()]);
        });

        SinkronTutorKeEduManage::dispatch($tutor->id_tutor);

        return $tutor;
    }

    /**
     * Sends the application back. Flagged sections get a note each; flagged
     * documents are marked ditolak with their reason and must be replaced.
     * Documents that were not flagged count as checked and valid.
     *
     * @param  array<string, string|null>  $bagian  section (LangkahPendaftaran value) => note
     * @param  array<string, string>  $dokumen  id_dokumen => reason
     */
    public function mintaPerbaikan(Tutor $tutor, AkunPengguna $admin, string $catatan, array $bagian, array $dokumen = []): Tutor
    {
        if ($dokumen !== [] && ! array_key_exists(LangkahPendaftaran::Dokumen->value, $bagian)) {
            $bagian[LangkahPendaftaran::Dokumen->value] = null;
        }

        if ($bagian === []) {
            throw new AksiDitolak('Tandai minimal satu bagian atau dokumen yang perlu diperbaiki.');
        }

        // Keep form order so the tutor reads the corrections top to bottom.
        $bagian = collect(LangkahPendaftaran::bagian())
            ->filter(fn (LangkahPendaftaran $langkah): bool => array_key_exists($langkah->value, $bagian))
            ->mapWithKeys(fn (LangkahPendaftaran $langkah): array => [$langkah->value => filled($bagian[$langkah->value]) ? $bagian[$langkah->value] : null])
            ->all();

        return $this->putuskan($tutor, $admin, StatusVerifikasiTutor::PerluPerbaikan, $catatan, $bagian, function (Tutor $tutor) use ($dokumen): void {
            foreach ($tutor->dokumen()->get() as $berkas) {
                $ditandai = array_key_exists($berkas->id_dokumen, $dokumen);

                if (! $ditandai && $berkas->status_verifikasi !== StatusVerifikasi::Menunggu) {
                    continue;
                }

                $berkas->forceFill([
                    'status_verifikasi' => $ditandai ? StatusVerifikasi::Ditolak : StatusVerifikasi::Valid,
                    'alasan_penolakan' => $ditandai ? $dokumen[$berkas->id_dokumen] : null,
                    'diperiksa_pada' => now(),
                ])->save();
            }
        });
    }

    /**
     * Final: the tutor cannot apply again with this account.
     */
    public function tolak(Tutor $tutor, AkunPengguna $admin, string $alasan): Tutor
    {
        return $this->putuskan($tutor, $admin, StatusVerifikasiTutor::Ditolak, $alasan, [], function (Tutor $tutor) use ($alasan): void {
            $tutor->dokumen()->where('status_verifikasi', StatusVerifikasi::Menunggu)
                ->update(['status_verifikasi' => StatusVerifikasi::Ditolak, 'alasan_penolakan' => $alasan, 'diperiksa_pada' => now(), 'updated_at' => now()]);
        });
    }

    /**
     * @param  array<string, string|null>  $bagian
     * @param  Closure(Tutor): void  $ubahDokumen
     */
    private function putuskan(Tutor $tutor, AkunPengguna $admin, StatusVerifikasiTutor $keputusan, ?string $catatan, array $bagian, Closure $ubahDokumen): Tutor
    {
        $tutor = DB::transaction(function () use ($tutor, $admin, $keputusan, $catatan, $bagian, $ubahDokumen): Tutor {
            $tutor = Tutor::query()->lockForUpdate()->findOrFail($tutor->id_tutor);

            if ($tutor->status_verifikasi !== StatusVerifikasiTutor::Menunggu) {
                throw new AksiDitolak("Pendaftaran {$tutor->nama} tidak sedang menunggu pemeriksaan, jadi keputusan ini tidak bisa dicatat.");
            }

            $ubahDokumen($tutor);

            $tutor->status_verifikasi = $keputusan;
            $tutor->catatan_verifikasi = $catatan;
            $tutor->bagian_perbaikan = $bagian === [] ? null : $bagian;
            $tutor->save();

            $tutor->riwayatVerifikasi()->create([
                'status' => $keputusan,
                'catatan' => $catatan,
                'bagian' => $bagian === [] ? null : $bagian,
                'id_akun' => $admin->id_akun,
                'dicatat_pada' => now(),
            ]);

            return $tutor;
        });

        Mail::to($tutor->kontak, $tutor->nama)->send(new KeputusanVerifikasiTutor($tutor, $keputusan, $catatan, $bagian));

        return $tutor;
    }
}
