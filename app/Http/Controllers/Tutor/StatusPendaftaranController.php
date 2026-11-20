<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read-only page while the application is with the admin (menunggu), or
 * after it was turned down (ditolak).
 */
class StatusPendaftaranController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tutor = $request->user()->tutor->load(['profil', 'mataPelajaran', 'dokumen' => fn ($dokumen) => $dokumen->orderBy('jenis'), 'jadwal' => fn ($jadwal) => $jadwal->orderBy('jam')]);

        return view('tutor.status', ['tutor' => $tutor]);
    }
}
