<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LaporanRingkasan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RingkasanController extends Controller
{
    public function __invoke(Request $request, LaporanRingkasan $laporan): View
    {
        $periode = (string) $request->query('periode');

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode)) {
            $periode = now()->format('Y-m');
        }

        return view('admin.ringkasan', ['ringkasan' => $laporan->untuk($periode)]);
    }
}
