<?php

namespace App\Http\Controllers\Tutor;

use App\Enums\Hari;
use App\Enums\StatusJadwal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\SlotRequest;
use App\Models\JadwalKetersediaan;
use App\Services\JadwalTutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalTutorController extends Controller
{
    public function __construct(private JadwalTutor $jadwalTutor) {}

    public function index(Request $request): View
    {
        $jadwal = $request->user()->tutor->jadwal()
            ->where('status', '!=', StatusJadwal::Nonaktif)
            ->get()
            ->sortBy(fn (JadwalKetersediaan $slot): string => $slot->hari->isoWeekday().$slot->jam)
            ->values();

        return view('tutor.dasbor.jadwal', ['daftarJadwal' => $jadwal, 'pilihanHari' => Hari::cases()]);
    }

    public function store(SlotRequest $request): RedirectResponse
    {
        $slot = $this->jadwalTutor->tambah($request->user()->tutor, Hari::from($request->validated('hari')), $request->validated('jam'));

        return redirect()->route('tutor.jadwal.index')->with('status', "Slot {$slot->label()} ditambahkan.");
    }

    public function update(SlotRequest $request, JadwalKetersediaan $jadwal): RedirectResponse
    {
        $slot = $this->jadwalTutor->ubah($jadwal, Hari::from($request->validated('hari')), $request->validated('jam'));

        return redirect()->route('tutor.jadwal.index')->with('status', "Slot diubah menjadi {$slot->label()}.");
    }

    public function destroy(JadwalKetersediaan $jadwal): RedirectResponse
    {
        $this->jadwalTutor->hapus($jadwal);

        return redirect()->route('tutor.jadwal.index')->with('status', "Slot {$jadwal->label()} dihapus.");
    }
}
