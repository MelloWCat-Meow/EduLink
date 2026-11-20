<?php

namespace App\Http\Controllers\Ortu;

use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ortu\SiswaRequest;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnakController extends Controller
{
    public function index(Request $request): View
    {
        $daftarAnak = $request->user()->siswa()
            ->withCount([
                'pesanan',
                'pesanan as pesanan_aktif_count' => fn ($pesanan) => $pesanan->whereIn('status', StatusPesanan::active()),
            ])
            ->orderBy('id_siswa')
            ->get();

        return view('ortu.anak.index', ['daftarAnak' => $daftarAnak]);
    }

    public function store(SiswaRequest $request): RedirectResponse
    {
        $akun = $request->user();
        $akun->siswa()->create([...$request->validated(), 'kontak_wali' => $akun->kontak]);

        return redirect()->route('anak.index')->with('status', $request->input('nama').' sudah ditambahkan.');
    }

    public function edit(Siswa $siswa): View
    {
        return view('ortu.anak.edit', ['siswa' => $siswa]);
    }

    public function update(SiswaRequest $request, Siswa $siswa): RedirectResponse
    {
        // kontak_wali always follows the account's current email.
        $siswa->update([...$request->validated(), 'kontak_wali' => $request->user()->kontak]);

        return redirect()->route('anak.index')->with('status', 'Data '.$siswa->nama.' sudah diperbarui.');
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        if ($siswa->pesanan()->whereIn('status', StatusPesanan::active())->exists()) {
            return back()->with('galat', $siswa->nama.' masih punya pesanan yang berjalan, jadi belum bisa dihapus. Selesaikan atau batalkan pesanannya dulu.');
        }

        if ($siswa->pesanan()->exists()) {
            return back()->with('galat', $siswa->nama.' punya riwayat pesanan, jadi datanya disimpan untuk catatan pembayaran dan ulasan.');
        }

        $siswa->delete();

        return redirect()->route('anak.index')->with('status', 'Data '.$siswa->nama.' sudah dihapus.');
    }
}
