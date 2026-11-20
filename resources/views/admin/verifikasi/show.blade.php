@use('App\Support\Format')
@use('App\Enums\StatusVerifikasi')

@php($adaMenunggu = $tutor->dokumen->contains('status_verifikasi', StatusVerifikasi::Menunggu))

<x-dasbor.layout :title="'Verifikasi '.$tutor->nama">
    <a href="{{ route('admin.verifikasi.index') }}" class="tautan mb-4 inline-block">Kembali ke antrean</a>
    <x-dasbor.judul :title="$tutor->nama" :note="$tutor->id_tutor.' · tutor eksternal · '.($tampilDiPencarian ? 'tampil di pencarian' : 'tidak tampil di pencarian')" />

    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] lg:items-start">
        <div class="space-y-8">
            <x-dasbor.panel heading="Berkas">
                <ul class="divide-y divide-pensil/12">
                    @foreach ($tutor->dokumen as $dokumen)
                        <li class="flex flex-wrap items-start justify-between gap-3 py-3">
                            <div>
                                <p class="font-bold">{{ $dokumen->jenis->label() }}</p>
                                <p class="text-[0.875rem] text-pensil-muda">{{ $dokumen->berkas }} · berkas contoh dari seeder, pratinjau belum tersedia</p>
                                @if ($dokumen->alasan_penolakan)
                                    <p class="mt-1 text-[0.9375rem]">Alasan ditolak: {{ $dokumen->alasan_penolakan }}</p>
                                @endif
                            </div>
                            <x-dasbor.label :tone="$dokumen->status_verifikasi->tone()">{{ $dokumen->status_verifikasi->label() }}</x-dasbor.label>
                        </li>
                    @endforeach
                </ul>
            </x-dasbor.panel>

            <x-dasbor.panel heading="Data tutor">
                <dl class="grid gap-x-6 gap-y-3 sm:grid-cols-2">
                    <div><dt class="text-[0.875rem] text-pensil-muda">Kontak</dt><dd class="font-bold">{{ $tutor->kontak }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Tarif</dt><dd class="angka font-bold">{{ $tutor->profil ? Format::rupiah($tutor->profil->tarif).' / jam' : '–' }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Kecamatan</dt><dd class="font-bold">{{ $tutor->profil?->lokasi ?? '–' }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Cara belajar</dt><dd class="font-bold">{{ $tutor->profil?->modesLabel() ?? '–' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-[0.875rem] text-pensil-muda">Mapel</dt><dd class="font-bold">{{ $tutor->mataPelajaran->map->label()->join(', ') ?: '–' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-[0.875rem] text-pensil-muda">Kompetensi</dt><dd>{{ $tutor->profil?->kompetensi ?? '–' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-[0.875rem] text-pensil-muda">Pengalaman</dt><dd>{{ $tutor->profil?->pengalaman ?? '–' }}</dd></div>
                </dl>
            </x-dasbor.panel>
        </div>

        <x-dasbor.panel heading="Keputusan">
            @if (! $adaMenunggu)
                <x-dasbor.kosong message="Tidak ada berkas yang menunggu pemeriksaan." />
            @else
                <form method="post" action="{{ route('admin.verifikasi.setujui', $tutor) }}">
                    @csrf
                    <p class="text-[0.9375rem]">Semua berkas yang menunggu akan ditandai valid, dan profil {{ $tutor->nama }} langsung muncul di pencarian.</p>
                    <button type="submit" class="tombol mt-3">Setujui berkas</button>
                </form>

                <form method="post" action="{{ route('admin.verifikasi.tolak', $tutor) }}" class="mt-7 border-t-2 border-dashed border-pensil/25 pt-5" novalidate>
                    @csrf
                    <x-dasbor.field name="alasan" label="Alasan menolak" hint="Dicatat pada setiap berkas yang ditolak.">
                        <textarea id="isian-alasan" name="alasan" maxlength="255" class="kontrol" aria-describedby="isian-alasan-petunjuk @error('alasan') isian-alasan-galat @enderror" @error('alasan') aria-invalid="true" @enderror>{{ old('alasan') }}</textarea>
                    </x-dasbor.field>
                    <button type="submit" class="tombol-garis tombol-bahaya mt-3">Tolak berkas</button>
                </form>
            @endif
        </x-dasbor.panel>
    </div>
</x-dasbor.layout>
