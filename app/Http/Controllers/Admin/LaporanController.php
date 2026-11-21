<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPembayaran;
use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Services\EksporLaporan;
use App\Services\LaporanRingkasan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * The admin summary for one period as a CSV download and as a print page
 * (save as PDF from the browser's print dialog; no PDF library).
 */
class LaporanController extends Controller
{
    public function csv(Request $request, EksporLaporan $ekspor): Response
    {
        $periode = $this->periode($request);

        return response($ekspor->csv($periode), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="laporan-edulink-'.$periode.'.csv"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function cetak(Request $request, LaporanRingkasan $laporan): View
    {
        $periode = $this->periode($request);

        return view('admin.laporan-cetak', [
            'ringkasan' => $laporan->untuk($periode),
            'daftarPesanan' => Pesanan::query()->whereBetween('created_at', EksporLaporan::rentang($periode))
                ->with(['siswa.wali', 'tutor', 'mapel'])
                ->withSum(['pembayaran as dibayar' => fn ($query) => $query->where('status', StatusPembayaran::Berhasil)], 'jumlah')
                ->orderBy('created_at')
                ->get(),
        ]);
    }

    private function periode(Request $request): string
    {
        $periode = (string) $request->query('periode');

        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode) ? $periode : now()->format('Y-m');
    }
}
