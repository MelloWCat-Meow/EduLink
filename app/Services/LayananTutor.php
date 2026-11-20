<?php

namespace App\Services;

use App\Models\Tutor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Changes a verified tutor may make without a new verification: the
 * practical profile fields and the payout account. Subjects and levels are
 * deliberately not here; the admin checked them, so only the admin changes them.
 */
class LayananTutor
{
    /**
     * A new rate applies to new orders only: each order keeps its own
     * tarif_per_jam and total_biaya from booking time.
     *
     * @param  array{tarif: int|string, lokasi: string, layanan: list<string>, pengalaman: string, kompetensi: string, no_hp: string}  $data
     */
    public function perbaruiProfil(Tutor $tutor, array $data, ?UploadedFile $foto = null): void
    {
        $fotoLama = null;

        DB::transaction(function () use ($tutor, $data, $foto, &$fotoLama): void {
            $profil = $tutor->profil()->firstOrFail();
            $profil->fill([
                'tarif' => $data['tarif'],
                'lokasi' => $data['lokasi'],
                'pengalaman' => $data['pengalaman'],
                'kompetensi' => $data['kompetensi'],
                'melayani_online' => in_array('online', $data['layanan'], true),
                'melayani_kunjungan' => in_array('kunjungan_rumah', $data['layanan'], true),
            ]);

            if ($foto !== null) {
                $fotoLama = $profil->foto;
                $profil->foto = $foto->store(PendaftaranTutor::FOLDER_FOTO, 'public');
            }

            $profil->save();
            $tutor->update(['no_hp' => $data['no_hp']]);
        });

        if ($fotoLama !== null) {
            Storage::disk('public')->delete($fotoLama);
        }
    }

    /**
     * @param  array{rekening_bank: string, rekening_nomor: string, rekening_atas_nama: string}  $data
     */
    public function simpanRekening(Tutor $tutor, array $data): void
    {
        $tutor->forceFill($data)->save();
    }
}
