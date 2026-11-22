<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Search filters. Invalid values are dropped instead of failing the search,
 * since the query string can come from old links.
 */
class CariTutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return array{mapel: ?string, jenjang: ?string, lokasi: ?string, tarif_min: ?int, tarif_maks: ?int, rating_min: ?int}
     */
    public function filter(): array
    {
        $angka = fn (string $kunci): ?int => filter_var($this->query($kunci), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) ?: null;
        $jenjang = strtoupper((string) $this->query('jenjang'));
        $rating = $angka('rating_min');

        return [
            'mapel' => $this->filled('mapel') ? (string) $this->query('mapel') : null,
            'jenjang' => in_array($jenjang, ['SD', 'SMP', 'SMA'], true) ? $jenjang : null,
            'lokasi' => in_array($this->query('lokasi'), config('landing.locations'), true) ? $this->query('lokasi') : null,
            'tarif_min' => $angka('tarif_min'),
            'tarif_maks' => $angka('tarif_maks'),
            'rating_min' => $rating !== null && $rating >= 1 && $rating <= 5 ? $rating : null,
        ];
    }
}
