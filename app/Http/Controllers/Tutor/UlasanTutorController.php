<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\RatingUlasan;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read-only: the reviews parents see on the public profile.
 */
class UlasanTutorController extends Controller
{
    public function __invoke(Request $request): View
    {
        $idTutor = $request->user()->tutor->id_tutor;

        $ulasan = RatingUlasan::query()
            ->where('ditampilkan', true)
            ->whereHas('pesanan', fn ($pesanan) => $pesanan->where('id_tutor', $idTutor))
            ->with(['pesanan.siswa', 'pesanan.mapel'])
            ->latest()
            ->paginate(10);

        return view('tutor.dasbor.ulasan', ['daftarUlasan' => $ulasan, 'rating' => Tutor::query()->denganRating()->findOrFail($idTutor)]);
    }
}
