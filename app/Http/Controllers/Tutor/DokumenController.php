<?php

namespace App\Http\Controllers\Tutor;

use App\Enums\JenisDokumen;
use App\Enums\LangkahPendaftaran;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\DokumenRequest;
use App\Models\DokumenVerifikasi;
use App\Services\PendaftaranTutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DokumenController extends Controller
{
    public function store(DokumenRequest $request, PendaftaranTutor $pendaftaran): RedirectResponse
    {
        $jenis = JenisDokumen::from($request->validated('jenis'));

        $pendaftaran->unggahDokumen($request->user()->tutor, $jenis, $request->file('berkas'));

        return redirect()->route('tutor.pendaftaran.langkah', LangkahPendaftaran::Dokumen)
            ->with('status', "{$jenis->label()} terunggah.");
    }

    public function destroy(Request $request, DokumenVerifikasi $dokumen, PendaftaranTutor $pendaftaran): RedirectResponse
    {
        $pendaftaran->hapusDokumen($request->user()->tutor, $dokumen);

        return redirect()->route('tutor.pendaftaran.langkah', LangkahPendaftaran::Dokumen)
            ->with('status', "{$dokumen->jenis->label()} dihapus.");
    }
}
