<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusKeluhan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TanggapiKeluhanRequest;
use App\Models\Keluhan;
use App\Services\Pemberitahu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KeluhanController extends Controller
{
    public function index(Request $request): View
    {
        $status = StatusKeluhan::tryFrom((string) $request->query('status'));

        $daftarKeluhan = Keluhan::query()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->with(['pengaju', 'pesanan.tutor'])
            ->orderByRaw("CASE status WHEN 'baru' THEN 0 WHEN 'diproses' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.keluhan.index', ['daftarKeluhan' => $daftarKeluhan, 'statusDipilih' => $status]);
    }

    public function show(Keluhan $keluhan): View
    {
        $keluhan->load(['pengaju', 'pesanan.tutor', 'pesanan.mapel', 'pesanan.siswa']);

        return view('admin.keluhan.show', ['keluhan' => $keluhan]);
    }

    public function update(TanggapiKeluhanRequest $request, Keluhan $keluhan, Pemberitahu $pemberitahu): RedirectResponse
    {
        $keluhan->status = StatusKeluhan::from($request->string('status')->toString());
        $keluhan->tanggapan_admin = $request->string('tanggapan_admin')->toString();
        $keluhan->save();

        $pemberitahu->keluhanDiperbarui($keluhan);

        return redirect()->route('admin.keluhan.show', $keluhan)->with('status', 'Tanggapan tersimpan dan terlihat oleh pengaju.');
    }
}
