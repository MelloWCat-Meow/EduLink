<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\TautanMeetingRequest;
use App\Models\Pesanan;
use App\Models\SesiPembelajaran;
use App\Services\TautanMeeting;
use Illuminate\Http\RedirectResponse;

class TautanMeetingController extends Controller
{
    public function __construct(private TautanMeeting $tautanMeeting) {}

    public function pesanan(TautanMeetingRequest $request, Pesanan $pesanan): RedirectResponse
    {
        $this->tautanMeeting->simpanUntukPesanan($pesanan, $request->tautan());

        return back()->with('status', $request->tautan() ? 'Tautan meeting tersimpan dan berlaku untuk semua sesi pesanan ini.' : 'Tautan meeting pesanan ini dihapus.');
    }

    public function sesi(TautanMeetingRequest $request, SesiPembelajaran $sesi): RedirectResponse
    {
        $this->tautanMeeting->simpanUntukSesi($sesi, $request->tautan());

        return back()->with('status', $request->tautan() ? 'Tautan khusus sesi ini tersimpan.' : 'Sesi ini kembali memakai tautan pesanan.');
    }
}
