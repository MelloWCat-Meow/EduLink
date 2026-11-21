@use('Illuminate\Support\Facades\Storage')

@php
    $profil = $tutor->profil;
    $layanan = old('layanan', array_map(fn ($mode) => $mode->value, $profil->modes()));
@endphp

<x-dasbor.layout title="Profil">
    <x-dasbor.judul title="Profil" note="perubahan langsung tampil di profil publik, tanpa verifikasi ulang">
        <x-slot:actions><a href="{{ route('tutor.show', $tutor) }}" class="tombol-garis">Lihat profil publik</a></x-slot:actions>
    </x-dasbor.judul>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] lg:items-start">
        <x-dasbor.panel heading="Data yang bisa Anda ubah">
            <form method="post" action="{{ route('tutor.profil.update') }}" enctype="multipart/form-data" class="space-y-5" novalidate>
                @csrf
                @method('PUT')
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-dasbor.field name="tarif" label="Tarif per jam (Rp)" hint="Rp25.000–Rp500.000. Hanya berlaku untuk pesanan baru.">
                        <input id="isian-tarif" name="tarif" inputmode="numeric" value="{{ old('tarif', (int) $profil->tarif) }}" class="kontrol" aria-describedby="isian-tarif-petunjuk @error('tarif') isian-tarif-galat @enderror" @error('tarif') aria-invalid="true" @enderror>
                    </x-dasbor.field>
                    <x-dasbor.field name="lokasi" label="Kecamatan">
                        <select id="isian-lokasi" name="lokasi" class="kontrol" @error('lokasi') aria-invalid="true" aria-describedby="isian-lokasi-galat" @enderror>
                            @foreach (config('landing.locations') as $kecamatan)
                                <option value="{{ $kecamatan }}" @selected(old('lokasi', $profil->lokasi) === $kecamatan)>{{ $kecamatan }}</option>
                            @endforeach
                        </select>
                    </x-dasbor.field>
                </div>

                <fieldset @error('layanan') aria-describedby="isian-layanan-galat" @enderror>
                    <legend class="font-bold">Layanan</legend>
                    <div class="mt-1 flex flex-wrap gap-x-6">
                        @foreach (['online' => 'Online', 'kunjungan_rumah' => 'Datang ke rumah siswa'] as $nilai => $teks)
                            <label class="flex min-h-11 items-center gap-2"><input type="checkbox" name="layanan[]" value="{{ $nilai }}" class="size-5 accent-tinta" @checked(in_array($nilai, $layanan, true))>{{ $teks }}</label>
                        @endforeach
                    </div>
                    <x-dasbor.koreksi name="layanan" />
                </fieldset>

                <x-dasbor.field name="kompetensi" label="Kompetensi">
                    <textarea id="isian-kompetensi" name="kompetensi" maxlength="1000" class="kontrol" @error('kompetensi') aria-invalid="true" aria-describedby="isian-kompetensi-galat" @enderror>{{ old('kompetensi', $profil->kompetensi) }}</textarea>
                </x-dasbor.field>
                <x-dasbor.field name="pengalaman" label="Pengalaman mengajar">
                    <textarea id="isian-pengalaman" name="pengalaman" maxlength="1000" class="kontrol" @error('pengalaman') aria-invalid="true" aria-describedby="isian-pengalaman-galat" @enderror>{{ old('pengalaman', $profil->pengalaman) }}</textarea>
                </x-dasbor.field>
                <x-dasbor.field name="no_hp" label="Nomor HP (WhatsApp)" hint="Hanya terlihat oleh orang tua dari pesanan yang sudah dibayar.">
                    <input id="isian-no_hp" name="no_hp" type="tel" inputmode="tel" value="{{ old('no_hp', $tutor->no_hp) }}" maxlength="20" class="kontrol max-w-[16rem]" aria-describedby="isian-no_hp-petunjuk @error('no_hp') isian-no_hp-galat @enderror" @error('no_hp') aria-invalid="true" @enderror>
                </x-dasbor.field>

                <div class="flex flex-wrap items-center gap-4">
                    @if ($profil->foto)
                        <img src="{{ Storage::disk('public')->url($profil->foto) }}" alt="Foto profil saat ini" class="size-20 object-cover">
                    @endif
                    <x-dasbor.field name="foto" label="Foto profil" hint="JPG atau PNG, maksimal 2 MB." class="min-w-0 flex-1">
                        <input id="isian-foto" type="file" name="foto" accept="image/jpeg,image/png" class="unggah" aria-describedby="isian-foto-petunjuk @error('foto') isian-foto-galat @enderror" @error('foto') aria-invalid="true" @enderror>
                    </x-dasbor.field>
                </div>

                <button type="submit" class="tombol">Simpan profil</button>
            </form>
        </x-dasbor.panel>

        <x-dasbor.panel heading="Mata pelajaran">
            <ul class="space-y-1">
                @foreach ($tutor->mataPelajaran->sortBy('nama')->groupBy('jenjang') as $jenjang => $daftar)
                    <li><span class="font-bold">{{ $jenjang }}:</span> {{ $daftar->pluck('nama')->join(', ') }}</li>
                @endforeach
            </ul>
            <p class="mt-4 border-l-4 border-tinta bg-langit/60 px-3 py-2 text-[0.9375rem]">Mata pelajaran dan jenjang sudah diperiksa tim akademik, jadi hanya admin yang bisa mengubahnya. Kirim permintaan ke <a href="mailto:{{ config('edulink.email_admin') }}?subject={{ rawurlencode('Ubah mata pelajaran '.$tutor->id_tutor) }}" class="tautan">{{ config('edulink.email_admin') }}</a>. Admin mengubahnya dari halaman Kelola tutor dengan alasan yang tercatat, lalu Anda menerima notifikasi.</p>
        </x-dasbor.panel>

        <x-dasbor.form-kata-sandi class="lg:col-start-2" />
    </div>
</x-dasbor.layout>
