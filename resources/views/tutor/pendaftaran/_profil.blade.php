@use('App\Enums\LangkahPendaftaran')
@use('App\Http\Requests\Tutor\ProfilTutorRequest')
@use('Illuminate\Support\Facades\Storage')

@php
    $profil = $tutor->profil;
    $riwayat = $profil?->riwayat_pendidikan ?? [];
    $mapelDipilih = old('mapel', $tutor->mataPelajaran->pluck('kode_mapel')->all());
    $jenjangDipilih = old('jenjang', $tutor->mataPelajaran->pluck('jenjang')->unique()->values()->all());
    $layanan = old('layanan', $profil ? array_map(fn ($mode) => $mode->value, $profil->modes()) : ['online']);
    $tarif = old('tarif', $profil?->tarif !== null ? (int) $profil->tarif : null);
@endphp

<x-tutor.bagian nomor="2" judul="Profil mengajar" :perbaiki="$tutor->perluDiperbaiki(LangkahPendaftaran::Profil)" :catatan="$tutor->catatanPerbaikan(LangkahPendaftaran::Profil)">
    <x-auth.error-summary />

    @if ($tutor->isInternal())
        <p class="mb-7 max-w-[40rem] border-l-4 border-tinta bg-langit/60 px-4 py-3">
            Pendidikan, kompetensi, pengalaman, dan mapel diambil dari data bimbel. Periksa, lalu lengkapi <strong>tarif per jam</strong>, <strong>kecamatan</strong>, dan <strong>layanan</strong>.
        </p>
    @endif

    <form method="POST" action="{{ route('tutor.pendaftaran.profil') }}" enctype="multipart/form-data" novalidate class="space-y-10" data-profil>
        @csrf
        @method('PUT')

        <fieldset class="space-y-6">
            <legend class="mb-4 font-display text-[1.15rem] font-extrabold">a. Riwayat pendidikan</legend>
            <div>
                <label for="isian-pendidikan" class="block font-bold">Pendidikan terakhir</label>
                <select id="isian-pendidikan" name="pendidikan" class="isian mt-1 max-w-[16rem]" @error('pendidikan') aria-invalid="true" aria-describedby="isian-pendidikan-galat" @enderror>
                    <option value="">Pilih</option>
                    @foreach (ProfilTutorRequest::PENDIDIKAN as $pilihan)
                        <option value="{{ $pilihan }}" @selected(old('pendidikan', $riwayat['pendidikan'] ?? null) === $pilihan)>{{ $pilihan }}</option>
                    @endforeach
                </select>
                <x-dasbor.koreksi name="pendidikan" />
            </div>
            <div class="grid gap-6 sm:grid-cols-2">
                <x-auth.field name="institusi" label="Kampus atau sekolah" maxlength="120" :value="$riwayat['institusi'] ?? null" />
                <x-auth.field name="jurusan" label="Jurusan atau program studi" maxlength="120" :value="$riwayat['jurusan'] ?? null" />
            </div>
        </fieldset>

        <fieldset class="space-y-6">
            <legend class="mb-4 font-display text-[1.15rem] font-extrabold">b. Kompetensi dan pengalaman</legend>
            @foreach (['kompetensi' => ['Kompetensi mengajar', 'Apa yang Anda kuasai dan cara Anda mengajar. Tampil di profil publik.'], 'pengalaman' => ['Pengalaman mengajar', 'Berapa tahun, jenjang apa, dan di mana.']] as $nama => [$label, $petunjuk])
                <div>
                    <label for="isian-{{ $nama }}" class="block font-bold">{{ $label }}</label>
                    <p id="isian-{{ $nama }}-petunjuk" class="text-[0.9375rem] text-pensil-muda">{{ $petunjuk }}</p>
                    <textarea id="isian-{{ $nama }}" name="{{ $nama }}" maxlength="1000" rows="4" class="isian-tulis mt-2"
                        aria-describedby="isian-{{ $nama }}-petunjuk @error($nama) isian-{{ $nama }}-galat @enderror" @error($nama) aria-invalid="true" @enderror>{{ old($nama, $profil?->{$nama}) }}</textarea>
                    <x-dasbor.koreksi :name="$nama" />
                </div>
            @endforeach
        </fieldset>

        <fieldset aria-describedby="isian-jenjang-petunjuk @error('jenjang') isian-jenjang-galat @enderror">
            <legend class="font-display text-[1.15rem] font-extrabold">c. Jenjang dan mata pelajaran</legend>
            <p id="isian-jenjang-petunjuk" class="mt-1 text-[0.9375rem] text-pensil-muda">Centang jenjang yang Anda ajar. Daftar mapel di bawahnya menyesuaikan.</p>
            <div class="mt-3 flex flex-wrap gap-x-7 gap-y-2">
                @foreach (ProfilTutorRequest::JENJANG as $jenjang)
                    <label class="flex min-h-11 items-center gap-3 font-bold">
                        <input type="checkbox" name="jenjang[]" value="{{ $jenjang }}" class="centang" data-jenjang-pilih="{{ $jenjang }}" @checked(in_array($jenjang, $jenjangDipilih, true))>
                        {{ $jenjang }}
                    </label>
                @endforeach
            </div>
            <x-dasbor.koreksi name="jenjang" />

            <div class="mt-5 grid gap-6 sm:grid-cols-3" id="isian-mapel" tabindex="-1">
                @foreach ($mapelPerJenjang as $jenjang => $daftar)
                    <fieldset data-jenjang-mapel="{{ $jenjang }}" class="border-t-2 border-dashed border-pensil/25 pt-3">
                        <legend class="font-bold">Mapel {{ $jenjang }}</legend>
                        <div class="mt-1 space-y-1">
                            @foreach ($daftar as $mapel)
                                <label class="flex min-h-11 items-center gap-3">
                                    <input type="checkbox" name="mapel[]" value="{{ $mapel->kode_mapel }}" class="centang" @checked(in_array($mapel->kode_mapel, $mapelDipilih, true))>
                                    {{ $mapel->nama }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
            </div>
            <x-dasbor.koreksi name="mapel" />
        </fieldset>

        <fieldset class="grid gap-6 sm:grid-cols-2">
            <legend class="mb-4 font-display text-[1.15rem] font-extrabold">d. Tarif dan wilayah</legend>
            <div>
                <label for="isian-tarif" class="block font-bold">Tarif per jam</label>
                <p id="isian-tarif-petunjuk" class="text-[0.9375rem] text-pensil-muda">Antara Rp25.000 dan Rp500.000.</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="font-tangan text-[1.375rem] text-tinta" aria-hidden="true">Rp</span>
                    <input id="isian-tarif" name="tarif" inputmode="numeric" autocomplete="off" value="{{ $tarif }}" class="isian-teks max-w-[12rem]"
                        aria-describedby="isian-tarif-petunjuk @error('tarif') isian-tarif-galat @enderror" @error('tarif') aria-invalid="true" @enderror>
                </div>
                <x-dasbor.koreksi name="tarif" />
            </div>
            <div>
                <label for="isian-lokasi" class="block font-bold">Kecamatan di Kota Jambi</label>
                <p id="isian-lokasi-petunjuk" class="text-[0.9375rem] text-pensil-muda">Tempat Anda mengajar atau tinggal.</p>
                <select id="isian-lokasi" name="lokasi" class="isian mt-1" aria-describedby="isian-lokasi-petunjuk @error('lokasi') isian-lokasi-galat @enderror" @error('lokasi') aria-invalid="true" @enderror>
                    <option value="">Pilih kecamatan</option>
                    @foreach (config('landing.locations') as $kecamatan)
                        <option value="{{ $kecamatan }}" @selected(old('lokasi', $profil?->lokasi) === $kecamatan)>{{ $kecamatan }}</option>
                    @endforeach
                </select>
                <x-dasbor.koreksi name="lokasi" />
            </div>
        </fieldset>

        <fieldset aria-describedby="@error('layanan') isian-layanan-galat @enderror">
            <legend class="font-display text-[1.15rem] font-extrabold">e. Layanan</legend>
            <p class="mt-1 text-[0.9375rem] text-pensil-muda">Pilih minimal satu.</p>
            <div class="mt-2 flex flex-wrap gap-x-8 gap-y-1">
                @foreach (['online' => 'Online (video call)', 'kunjungan_rumah' => 'Datang ke rumah siswa'] as $nilai => $label)
                    <label class="flex min-h-11 items-center gap-3 font-bold">
                        <input type="checkbox" name="layanan[]" value="{{ $nilai }}" class="centang" @checked(in_array($nilai, $layanan, true))>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            <x-dasbor.koreksi name="layanan" />
        </fieldset>

        <fieldset>
            <legend class="font-display text-[1.15rem] font-extrabold">f. Foto profil <span class="font-teks text-[0.9375rem] font-normal text-pensil-muda">(opsional)</span></legend>
            <div class="mt-3 flex flex-wrap items-center gap-5">
                @if ($profil?->foto)
                    <img src="{{ Storage::disk('public')->url($profil->foto) }}" alt="Foto profil saat ini" class="size-24 rotate-[-2deg] border-4 border-white object-cover shadow-[0_8px_18px_-10px_rgb(0_0_0/0.5)]">
                @endif
                <div class="min-w-0 flex-1">
                    <label for="isian-foto" class="block font-bold">{{ $profil?->foto ? 'Ganti foto' : 'Pilih foto' }}</label>
                    <p id="isian-foto-petunjuk" class="text-[0.9375rem] text-pensil-muda">JPG atau PNG, maksimal 2 MB. Wajah terlihat jelas.</p>
                    <input id="isian-foto" type="file" name="foto" accept="image/jpeg,image/png" class="unggah mt-2"
                        aria-describedby="isian-foto-petunjuk @error('foto') isian-foto-galat @enderror" @error('foto') aria-invalid="true" @enderror>
                    <x-dasbor.koreksi name="foto" />
                </div>
            </div>
        </fieldset>

        <div class="pt-2">@include('tutor.pendaftaran._tombol')</div>
    </form>
</x-tutor.bagian>

<script>
    // Show only the subject lists of the levels that are ticked; unticking a level clears its subjects.
    (() => {
        const form = document.querySelector('[data-profil]');
        if (!form) return;
        const sinkron = () => {
            form.querySelectorAll('[data-jenjang-pilih]').forEach((kotak) => {
                const grup = form.querySelector(`[data-jenjang-mapel="${kotak.dataset.jenjangPilih}"]`);
                if (!grup) return;
                grup.hidden = !kotak.checked;
                if (!kotak.checked) grup.querySelectorAll('input').forEach((mapel) => (mapel.checked = false));
            });
        };
        form.addEventListener('change', (e) => { if (e.target.matches('[data-jenjang-pilih]')) sinkron(); });
        sinkron();
    })();
</script>
