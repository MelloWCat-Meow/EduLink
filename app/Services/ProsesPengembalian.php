<?php

namespace App\Services;

use App\Enums\StatusPengembalian;
use App\Exceptions\AksiDitolak;
use App\Models\AkunPengguna;
use App\Models\PengembalianDana;
use Illuminate\Support\Facades\DB;

/**
 * The admin records that a refund was transferred to the parent.
 */
class ProsesPengembalian
{
    public function __construct(private Pemberitahu $pemberitahu) {}

    public function tandaiSelesai(PengembalianDana $pengembalian, AkunPengguna $admin, string $catatan): void
    {
        DB::transaction(function () use ($pengembalian, $admin, $catatan): void {
            $terkunci = PengembalianDana::query()->lockForUpdate()->findOrFail($pengembalian->id_pengembalian);

            if ($terkunci->status !== StatusPengembalian::PerluDiproses) {
                throw new AksiDitolak('Pengembalian ini sudah ditandai selesai.');
            }

            $terkunci->update(['status' => StatusPengembalian::Selesai, 'catatan' => $catatan, 'diproses_oleh' => $admin->id_akun, 'diproses_pada' => now()]);
            $pengembalian->setRawAttributes($terkunci->getAttributes(), true);

            $this->pemberitahu->pengembalianSelesai($terkunci);
        });
    }
}
