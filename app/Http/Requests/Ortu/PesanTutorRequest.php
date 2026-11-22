<?php

namespace App\Http\Requests\Ortu;

use App\Enums\ModeBelajar;
use App\Models\JadwalKetersediaan;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\Tutor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PesanTutorRequest extends FormRequest
{
    public const PILIHAN_DURASI = [60, 90, 120];

    public const MAKS_PERTEMUAN = 12;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Tutor $tutor */
        $tutor = $this->route('tutor');

        return [
            'id_siswa' => ['required', 'string', Rule::exists('siswa', 'id_siswa')->where('id_akun', $this->user()->id_akun)],
            'kode_mapel' => ['required', 'string', Rule::exists('tutor_mata_pelajaran', 'kode_mapel')->where('id_tutor', $tutor->id_tutor)],
            'id_jadwal' => ['required', 'string', Rule::exists('jadwal_ketersediaan', 'id_jadwal')->where('id_tutor', $tutor->id_tutor)],
            'mode' => ['required', Rule::enum(ModeBelajar::class)],
            'durasi' => ['required', 'integer', Rule::in(self::PILIHAN_DURASI)],
            'tanggal_mulai' => ['required', 'date_format:Y-m-d', 'after:today', 'before_or_equal:'.now()->addDays(60)->toDateString()],
            'jumlah_pertemuan' => ['required', 'integer', 'min:1', 'max:'.self::MAKS_PERTEMUAN],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Tutor $tutor */
                $tutor = $this->route('tutor');
                $errors = $validator->errors();

                if (! $errors->has('mode') && ! $tutor->profil->offers(ModeBelajar::from($this->input('mode')))) {
                    $errors->add('mode', 'Tutor ini tidak melayani cara belajar tersebut.');
                }

                if (! $errors->has('id_siswa') && ! $errors->has('kode_mapel')) {
                    $siswa = Siswa::query()->findOrFail($this->input('id_siswa'));
                    $mapel = MataPelajaran::query()->findOrFail($this->input('kode_mapel'));

                    if ($siswa->jenjang !== $mapel->jenjang) {
                        $errors->add('kode_mapel', "Mapel ini untuk jenjang {$mapel->jenjang}, sedangkan {$siswa->nama} di {$siswa->jenjang}.");
                    }
                }

                if (! $errors->has('id_jadwal') && ! $errors->has('tanggal_mulai')) {
                    $hari = JadwalKetersediaan::query()->findOrFail($this->input('id_jadwal'))->hari;

                    if (Carbon::parse($this->input('tanggal_mulai'))->isoWeekday() !== $hari->isoWeekday()) {
                        $errors->add('tanggal_mulai', "Tanggal mulai harus hari {$hari->label()}, sesuai jadwal yang dipilih.");
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id_siswa.required' => 'Pilih anak yang akan belajar.',
            'id_siswa.exists' => 'Pilih anak dari daftar anak Anda.',
            'kode_mapel.required' => 'Pilih mapel.',
            'kode_mapel.exists' => 'Tutor ini tidak mengajar mapel tersebut.',
            'id_jadwal.required' => 'Pilih jadwal.',
            'id_jadwal.exists' => 'Jadwal itu bukan milik tutor ini.',
            'mode.required' => 'Pilih cara belajar.',
            'mode.enum' => 'Pilih online atau tutor datang ke rumah.',
            'durasi.required' => 'Pilih lama tiap sesi.',
            'durasi.in' => 'Lama sesi harus 60, 90, atau 120 menit.',
            'tanggal_mulai.required' => 'Isi tanggal sesi pertama.',
            'tanggal_mulai.date_format' => 'Tulis tanggal dengan format yang benar.',
            'tanggal_mulai.after' => 'Tanggal mulai paling cepat besok.',
            'tanggal_mulai.before_or_equal' => 'Tanggal mulai paling lambat 60 hari dari sekarang.',
            'jumlah_pertemuan.required' => 'Isi jumlah pertemuan.',
            'jumlah_pertemuan.integer' => 'Jumlah pertemuan harus angka bulat.',
            'jumlah_pertemuan.min' => 'Minimal 1 pertemuan.',
            'jumlah_pertemuan.max' => 'Maksimal '.self::MAKS_PERTEMUAN.' pertemuan dalam satu pesanan.',
        ];
    }

    /**
     * @return array{siswa: Siswa, tutor: Tutor, mapel: MataPelajaran, id_jadwal: string, mode: ModeBelajar, durasi: int, tanggal_mulai: Carbon, jumlah_pertemuan: int}
     */
    public function dataPesanan(): array
    {
        return [
            'siswa' => Siswa::query()->findOrFail($this->input('id_siswa')),
            'tutor' => $this->route('tutor'),
            'mapel' => MataPelajaran::query()->findOrFail($this->input('kode_mapel')),
            'id_jadwal' => $this->input('id_jadwal'),
            'mode' => ModeBelajar::from($this->input('mode')),
            'durasi' => $this->integer('durasi'),
            'tanggal_mulai' => Carbon::parse($this->input('tanggal_mulai')),
            'jumlah_pertemuan' => $this->integer('jumlah_pertemuan'),
        ];
    }
}
