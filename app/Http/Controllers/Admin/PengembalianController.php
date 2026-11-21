<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPengembalian;
use App\Http\Controllers\Controller;
use App\Models\PengembalianDana;
use App\Services\ProsesPengembalian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengembalianController extends Controller
{
    public function index(Request $request): View
    {
        $status = StatusPengembalian::tryFrom((string) $request->query('status'));

        $daftar = PengembalianDana::query()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->with(['pesanan.siswa.wali', 'pesanan.tutor', 'pesanan.mapel', 'pemroses'])
            ->orderByRaw("CASE status WHEN 'perlu_diproses' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.pengembalian', ['daftar' => $daftar, 'statusDipilih' => $status]);
    }

    public function selesai(Request $request, PengembalianDana $pengembalian, ProsesPengembalian $proses): RedirectResponse
    {
        $data = $request->validate(
            ['catatan' => ['required', 'string', 'min:5', 'max:255']],
            ['catatan.required' => 'Tulis catatan transfer, misalnya bank, nama penerima, dan tanggal.', 'catatan.min' => 'Catatan terlalu singkat.'],
        );

        $proses->tandaiSelesai($pengembalian, $request->user(), $data['catatan']);

        return back()->with('status', "Pengembalian {$pengembalian->id_pengembalian} ditandai selesai. Orang tua sudah diberi tahu.");
    }
}
