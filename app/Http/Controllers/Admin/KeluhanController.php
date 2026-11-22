<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusKeluhan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TanggapiKeluhanRequest;
use App\Models\Keluhan;
use App\Services\KonfirmasiSesi;
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
        $keluhan->load(['pengaju', 'pesanan.tutor', 'pesanan.mapel', 'pesanan.siswa', 'sesi']);

        return view('admin.keluhan.show', ['keluhan' => $keluhan]);
    }

    /**
     * Settles a disputed session: valid (the tutor is paid) or not valid
     * (the parent gets the session's value back). Closes this complaint.
     */
    public function putuskan(Request $request, Keluhan $keluhan, KonfirmasiSesi $konfirmasiSesi): RedirectResponse
    {
        abort_if($keluhan->id_sesi === null, 404);

        $data = $request->validate([
            'keputusan' => ['required', 'in:sah,tidak_sah'],
            'alasan' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'keputusan.required' => 'Pilih sesi sah atau tidak sah.',
            'keputusan.in' => 'Pilih sesi sah atau tidak sah.',
            'alasan.required' => 'Tulis alasan keputusan. Alasan ini dikirim ke orang tua dan tutor.',
            'alasan.min' => 'Alasan terlalu singkat, minimal 5 karakter.',
        ]);

        $konfirmasiSesi->putuskan($keluhan->sesi()->firstOrFail(), $request->user(), $data['keputusan'] === 'sah', $data['alasan']);

        return redirect()->route('admin.keluhan.show', $keluhan)->with('status', $data['keputusan'] === 'sah'
            ? 'Sesi dinyatakan sah. Bagian tutor untuk sesi ini masuk saldo.'
            : 'Sesi dinyatakan tidak sah. Pengembalian dana dicatat untuk orang tua.');
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
