<?php

namespace App\Http\Controllers;

use App\Enums\StatusJadwal;
use App\Http\Requests\CariTutorRequest;
use App\Models\MataPelajaran;
use App\Models\RatingUlasan;
use App\Models\Tutor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

/**
 * Public tutor search and profiles. Only listed tutors (internal, or with all
 * documents approved) are shown; anything else is a 404.
 */
class TutorController extends Controller
{
    public const PER_HALAMAN = 6;

    public function index(CariTutorRequest $request): View
    {
        $filter = $request->filter();

        $daftarTutor = Tutor::query()
            ->tampil()
            ->denganRating()
            ->with(['profil', 'mataPelajaran' => fn ($mapel) => $mapel->orderBy('jenjang')->orderBy('nama')])
            ->when($filter['mapel'] || $filter['jenjang'], fn (Builder $query) => $query->whereHas('mataPelajaran', function (Builder $mapel) use ($filter): void {
                $mapel->when($filter['mapel'], fn (Builder $q) => $q->where('nama', $filter['mapel']))
                    ->when($filter['jenjang'], fn (Builder $q) => $q->where('jenjang', $filter['jenjang']));
            }))
            ->when($filter['lokasi'], fn (Builder $query) => $query->whereRelation('profil', 'lokasi', $filter['lokasi']))
            ->when($filter['tarif_min'], fn (Builder $query) => $query->whereRelation('profil', 'tarif', '>=', $filter['tarif_min']))
            ->when($filter['tarif_maks'], fn (Builder $query) => $query->whereRelation('profil', 'tarif', '<=', $filter['tarif_maks']))
            ->when($filter['rating_min'], fn (Builder $query) => $query->where(
                RatingUlasan::query()
                    ->selectRaw('AVG(rating_ulasan.nilai)')
                    ->join('pesanan', 'pesanan.id_pesanan', '=', 'rating_ulasan.id_pesanan')
                    ->whereColumn('pesanan.id_tutor', 'tutor.id_tutor')
                    ->where('rating_ulasan.ditampilkan', true),
                '>=',
                $filter['rating_min'],
            ))
            ->orderByDesc('rata_rating')
            ->orderBy('nama')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('tutor.index', [
            'daftarTutor' => $daftarTutor,
            'filter' => $filter,
            'pilihanMapel' => MataPelajaran::query()->distinct()->orderBy('nama')->pluck('nama'),
            'pilihanLokasi' => config('landing.locations'),
        ]);
    }

    public function show(string $tutor): View
    {
        $tutor = Tutor::query()
            ->tampil()
            ->denganRating()
            ->with([
                'profil',
                'mataPelajaran' => fn ($mapel) => $mapel->orderBy('jenjang')->orderBy('nama'),
                'jadwal' => fn ($jadwal) => $jadwal->orderBy('jam'),
            ])
            ->findOrFail($tutor);

        $ulasan = $tutor->ulasan()
            ->where('ditampilkan', true)
            ->with('pesanan.mapel')
            ->latest('rating_ulasan.created_at')
            ->limit(10)
            ->get();

        return view('tutor.show', [
            'tutor' => $tutor,
            'ulasan' => $ulasan,
            'jadwalTersedia' => $tutor->jadwal->where('status', StatusJadwal::Tersedia)->sortBy(fn ($jadwal) => $jadwal->hari->isoWeekday())->values(),
            'jumlahJadwalTerisi' => $tutor->jadwal->where('status', '!=', StatusJadwal::Tersedia)->count(),
        ]);
    }
}
