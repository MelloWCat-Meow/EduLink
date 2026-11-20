<?php

namespace App\Http\Controllers;

use App\Models\DokumenVerifikasi;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a verification document from the private disk. The route checks
 * DokumenVerifikasiPolicy first: the owning tutor and admins only.
 */
class BerkasDokumenController extends Controller
{
    public function __invoke(DokumenVerifikasi $dokumen): StreamedResponse
    {
        $disk = Storage::disk('local');

        abort_unless($disk->exists($dokumen->berkas), 404);

        $nama = $dokumen->jenis->value.'-'.$dokumen->id_tutor.'.'.pathinfo($dokumen->berkas, PATHINFO_EXTENSION);

        return $disk->response($dokumen->berkas, $nama, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], 'inline');
    }
}
