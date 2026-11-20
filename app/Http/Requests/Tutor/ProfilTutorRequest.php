<?php

namespace App\Http\Requests\Tutor;

use App\Models\MataPelajaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

/**
 * Step 2: education, experience, subjects per level, rate, district and modes.
 */
class ProfilTutorRequest extends FormRequest
{
    public const TARIF_MIN = 25000;

    public const TARIF_MAKS = 500000;

    /** @var list<string> */
    public const PENDIDIKAN = ['SMA/SMK', 'D3', 'D4', 'S1', 'S2', 'S3'];

    /** @var list<string> */
    public const JENJANG = ['SD', 'SMP', 'SMA'];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tarif' => preg_replace('/\D/', '', (string) $this->input('tarif')),
            'institusi' => trim((string) $this->input('institusi')),
            'jurusan' => trim((string) $this->input('jurusan')),
            'kompetensi' => trim((string) $this->input('kompetensi')),
            'pengalaman' => trim((string) $this->input('pengalaman')),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'pendidikan' => ['required', Rule::in(self::PENDIDIKAN)],
            'institusi' => ['required', 'string', 'max:120'],
            'jurusan' => ['required', 'string', 'max:120'],
            'kompetensi' => ['required', 'string', 'min:20', 'max:1000'],
            'pengalaman' => ['required', 'string', 'min:20', 'max:1000'],
            'jenjang' => ['required', 'array', 'min:1'],
            'jenjang.*' => [Rule::in(self::JENJANG)],
            'mapel' => ['required', 'array', 'min:1'],
            'mapel.*' => ['string', 'distinct', Rule::exists('mata_pelajaran', 'kode_mapel')],
            'tarif' => ['required', 'integer', 'between:'.self::TARIF_MIN.','.self::TARIF_MAKS],
            'lokasi' => ['required', Rule::in(config('landing.locations'))],
            'layanan' => ['required', 'array', 'min:1'],
            'layanan.*' => [Rule::in(['online', 'kunjungan_rumah'])],
            'foto' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png'])->max(2048)],
        ];
    }

    /**
     * Every chosen subject must belong to one of the chosen levels.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['mapel', 'mapel.*', 'jenjang', 'jenjang.*'])) {
                return;
            }

            $diluar = MataPelajaran::query()->whereIn('kode_mapel', $this->input('mapel'))->whereNotIn('jenjang', $this->input('jenjang'))->exists();

            if ($diluar) {
                $validator->errors()->add('mapel', 'Ada mata pelajaran di luar jenjang yang Anda pilih. Centang jenjangnya, atau hapus mapel itu.');
            }
        }];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pendidikan.required' => 'Pilih pendidikan terakhir.',
            'pendidikan.in' => 'Pilih pendidikan terakhir dari daftar.',
            'institusi.required' => 'Tulis nama kampus atau sekolah.',
            'jurusan.required' => 'Tulis jurusan atau program studi.',
            'kompetensi.required' => 'Ceritakan kompetensi mengajar Anda.',
            'kompetensi.min' => 'Ceritakan sedikit lebih panjang, minimal 20 karakter.',
            'pengalaman.required' => 'Ceritakan pengalaman mengajar Anda.',
            'pengalaman.min' => 'Ceritakan sedikit lebih panjang, minimal 20 karakter.',
            'jenjang.required' => 'Pilih minimal satu jenjang yang Anda ajar.',
            'jenjang.*.in' => 'Pilih SD, SMP, atau SMA.',
            'mapel.required' => 'Pilih minimal satu mata pelajaran.',
            'mapel.*.exists' => 'Mata pelajaran itu tidak ada di daftar.',
            'tarif.required' => 'Tulis tarif per jam.',
            'tarif.integer' => 'Tulis tarif dengan angka saja, misalnya 75000.',
            'tarif.between' => 'Tarif per jam antara Rp25.000 dan Rp500.000.',
            'lokasi.required' => 'Pilih kecamatan tempat Anda mengajar.',
            'lokasi.in' => 'Pilih kecamatan di Kota Jambi dari daftar.',
            'layanan.required' => 'Pilih minimal satu layanan: online atau kunjungan rumah.',
            'foto.image' => 'Foto profil harus berupa gambar JPG atau PNG.',
            'foto.mimes' => 'Foto profil harus berupa gambar JPG atau PNG.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}
