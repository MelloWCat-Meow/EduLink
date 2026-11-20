@use('App\Support\Format')

@php($layout = auth()->user()?->peran === \App\Enums\Peran::Ortu ? 'dasbor.layout' : 'publik.layout')

<x-dynamic-component :component="$layout" title="Cari tutor">
    <x-dasbor.judul title="Cari tutor" note="hanya tutor internal dan tutor yang berkasnya sudah diperiksa" />

    <div class="grid gap-8 lg:grid-cols-[17rem_minmax(0,1fr)] lg:items-start">
        <x-dasbor.panel heading="Saring">
            <form method="get" action="{{ route('tutor.index') }}" class="space-y-4">
                <x-dasbor.field name="mapel" label="Mapel">
                    <select id="isian-mapel" name="mapel" class="kontrol">
                        <option value="">Semua mapel</option>
                        @foreach ($pilihanMapel as $mapel)
                            <option value="{{ $mapel }}" @selected($filter['mapel'] === $mapel)>{{ $mapel }}</option>
                        @endforeach
                    </select>
                </x-dasbor.field>

                <x-dasbor.field name="jenjang" label="Jenjang">
                    <select id="isian-jenjang" name="jenjang" class="kontrol">
                        <option value="">Semua jenjang</option>
                        @foreach (['SD', 'SMP', 'SMA'] as $jenjang)
                            <option value="{{ strtolower($jenjang) }}" @selected($filter['jenjang'] === $jenjang)>{{ $jenjang }}</option>
                        @endforeach
                    </select>
                </x-dasbor.field>

                <x-dasbor.field name="lokasi" label="Kecamatan tutor">
                    <select id="isian-lokasi" name="lokasi" class="kontrol">
                        <option value="">Seluruh Kota Jambi</option>
                        @foreach ($pilihanLokasi as $lokasi)
                            <option value="{{ $lokasi }}" @selected($filter['lokasi'] === $lokasi)>{{ $lokasi }}</option>
                        @endforeach
                    </select>
                </x-dasbor.field>

                <fieldset>
                    <legend class="font-bold">Tarif per jam (Rp)</legend>
                    <div class="mt-1.5 grid grid-cols-2 gap-2">
                        <label class="block text-[0.875rem]">Dari
                            <input name="tarif_min" type="number" min="0" step="5000" inputmode="numeric" value="{{ $filter['tarif_min'] }}" placeholder="50000" class="kontrol mt-0.5">
                        </label>
                        <label class="block text-[0.875rem]">Sampai
                            <input name="tarif_maks" type="number" min="0" step="5000" inputmode="numeric" value="{{ $filter['tarif_maks'] }}" placeholder="120000" class="kontrol mt-0.5">
                        </label>
                    </div>
                </fieldset>

                <x-dasbor.field name="rating_min" label="Rating minimal">
                    <select id="isian-rating_min" name="rating_min" class="kontrol">
                        <option value="">Semua rating</option>
                        @foreach ([4, 3, 2, 1] as $nilai)
                            <option value="{{ $nilai }}" @selected($filter['rating_min'] === $nilai)>{{ $nilai }} ke atas</option>
                        @endforeach
                    </select>
                </x-dasbor.field>

                <div class="flex flex-wrap items-center gap-4 pt-1">
                    <button type="submit" class="tombol">Tampilkan</button>
                    <a href="{{ route('tutor.index') }}" class="tautan">Hapus saringan</a>
                </div>
            </form>
        </x-dasbor.panel>

        <section aria-labelledby="hasil-cari">
            <h2 id="hasil-cari" class="mb-4 font-bold" role="status">
                {{ $daftarTutor->total() }} tutor ditemukan
            </h2>

            @if ($daftarTutor->isEmpty())
                <x-dasbor.panel>
                    <x-dasbor.kosong message="Belum ada tutor yang cocok dengan saringan ini. Coba longgarkan tarif atau pilih kecamatan lain.">
                        <a href="{{ route('tutor.index') }}" class="tombol-garis">Lihat semua tutor</a>
                    </x-dasbor.kosong>
                </x-dasbor.panel>
            @else
                <ul class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($daftarTutor as $tutor)
                        <li class="relative flex flex-col bg-white px-5 py-5 shadow-[0_1px_0_rgb(0_0_0/0.05),0_14px_30px_-24px_rgb(37_37_43/0.5)] focus-within:outline-3 focus-within:outline-tinta hover:-translate-y-0.5 motion-safe:transition-transform">
                            <p class="text-[0.8125rem] font-bold text-pensil-muda">{{ $tutor->status === \App\Enums\StatusTutor::Internal ? 'Tutor EduLink' : 'Berkas sudah diperiksa' }}</p>
                            <h3 class="mt-0.5 font-display text-[1.35rem] leading-tight font-extrabold [font-stretch:90%]">
                                <a href="{{ route('tutor.show', $tutor) }}" class="outline-none after:absolute after:inset-0">{{ $tutor->nama }}</a>
                            </h3>
                            <x-tutor.rating :rata="$tutor->rata_rating" :jumlah="$tutor->jumlah_ulasan" class="mt-1 text-[0.9375rem]" />
                            <p class="mt-3 text-[0.9375rem]">{{ $tutor->mataPelajaran->map->label()->join(', ') }}</p>
                            <p class="mt-1 text-[0.9375rem] text-pensil-muda">{{ $tutor->profil->lokasi }} · {{ $tutor->profil->modesLabel() }}</p>
                            <p class="mt-auto pt-4"><span class="angka font-display text-[1.3rem] font-extrabold">{{ Format::rupiah($tutor->profil->tarif) }}</span> <span class="text-pensil-muda">/ jam</span></p>
                        </li>
                    @endforeach
                </ul>
                {{ $daftarTutor->links('components.dasbor.halaman') }}
            @endif
        </section>
    </div>
</x-dynamic-component>
