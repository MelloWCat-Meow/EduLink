<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ModerasiUlasanRequest;
use App\Models\RatingUlasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UlasanController extends Controller
{
    public function index(Request $request): View
    {
        $tampil = match ($request->query('tampil')) {
            'ya' => true,
            'tidak' => false,
            default => null,
        };

        $daftarUlasan = RatingUlasan::query()
            ->when($tampil !== null, fn ($query) => $query->where('ditampilkan', $tampil))
            ->with(['pesanan.tutor', 'pesanan.siswa.wali'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.ulasan.index', ['daftarUlasan' => $daftarUlasan, 'tampil' => $tampil]);
    }

    public function update(ModerasiUlasanRequest $request, RatingUlasan $ulasan): RedirectResponse
    {
        $ulasan->forceFill([
            'ditampilkan' => $request->boolean('ditampilkan'),
            'alasan_moderasi' => $request->string('alasan')->toString(),
        ])->save();

        return back()->with('status', "Ulasan {$ulasan->id_ulasan} ".($ulasan->ditampilkan ? 'ditampilkan lagi' : 'disembunyikan').'. Rating rata-rata tutor ikut diperbarui.');
    }
}
