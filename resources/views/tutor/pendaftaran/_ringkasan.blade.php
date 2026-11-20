@use('App\Enums\LangkahPendaftaran')
@use('App\Support\Format')

{{--
    Read-only summary of everything the tutor filled in. $bisaUbah adds a link
    to each step; $kekurangan marks the sections that are still missing.
--}}
@php
    $profil = $tutor->profil;
    $bisaUbah ??= false;
    $kekurangan ??= [];
@endphp

<div class="space-y-8">
    @foreach (LangkahPendaftaran::bagian() as $langkah)
        <section aria-labelledby="ringkasan-{{ $langkah->value }}">
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 border-b-2 border-pensil pb-1.5">
                <h3 id="ringkasan-{{ $langkah->value }}" class="font-display text-[1.2rem] font-extrabold">
                    <span class="font-tangan text-tinta">{{ $langkah->nomor() }}.</span> {{ $langkah->label() }}
                    @if (array_key_exists($langkah->value, $kekurangan))
                        <x-dasbor.label tone="buruk" class="ml-2 align-middle">Belum lengkap</x-dasbor.label>
                    @endif
                </h3>
                @if ($bisaUbah)
                    <a href="{{ route('tutor.pendaftaran.langkah', $langkah) }}" class="tautan">Ubah<span class="sr-only"> {{ $langkah->label() }}</span></a>
                @endif
            </div>

            @if ($langkah === LangkahPendaftaran::DataDiri)
                <dl class="mt-3 grid gap-x-6 gap-y-3 sm:grid-cols-3">
                    <div><dt class="text-[0.875rem] text-pensil-muda">Nama</dt><dd class="font-bold">{{ $tutor->nama }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Email</dt><dd class="font-bold break-all">{{ $tutor->kontak }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Nomor HP</dt><dd class="angka font-bold">{{ $tutor->no_hp ?? '–' }}</dd></div>
                </dl>
            @elseif ($langkah === LangkahPendaftaran::Profil)
                <dl class="mt-3 grid gap-x-6 gap-y-3 sm:grid-cols-3">
                    <div class="sm:col-span-3"><dt class="text-[0.875rem] text-pensil-muda">Pendidikan terakhir</dt><dd class="font-bold">{{ $profil?->pendidikanLabel() ?? '–' }}</dd></div>
                    <div class="sm:col-span-3"><dt class="text-[0.875rem] text-pensil-muda">Kompetensi</dt><dd>{{ $profil?->kompetensi ?: '–' }}</dd></div>
                    <div class="sm:col-span-3"><dt class="text-[0.875rem] text-pensil-muda">Pengalaman</dt><dd>{{ $profil?->pengalaman ?: '–' }}</dd></div>
                    <div class="sm:col-span-3">
                        <dt class="text-[0.875rem] text-pensil-muda">Mata pelajaran</dt>
                        <dd>
                            @forelse ($tutor->mataPelajaran->sortBy('nama')->groupBy('jenjang') as $jenjang => $daftar)
                                <span class="block"><span class="font-bold">{{ $jenjang }}:</span> {{ $daftar->pluck('nama')->join(', ') }}</span>
                            @empty
                                –
                            @endforelse
                        </dd>
                    </div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Tarif per jam</dt><dd class="angka font-bold">{{ $profil?->tarif !== null ? Format::rupiah($profil->tarif) : 'Belum diisi' }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Kecamatan</dt><dd class="font-bold">{{ $profil?->lokasi ?: 'Belum diisi' }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Layanan</dt><dd class="font-bold">{{ $profil?->modes() ? $profil->modesLabel() : '–' }}</dd></div>
                </dl>
            @elseif ($langkah === LangkahPendaftaran::Dokumen)
                @if ($tutor->dokumen->isEmpty())
                    <p class="mt-3">{{ $tutor->isInternal() ? 'Tidak ada dokumen baru (opsional untuk pengajar bimbel).' : 'Belum ada dokumen.' }}</p>
                @else
                    <ul class="mt-3 divide-y divide-pensil/12">
                        @foreach ($tutor->dokumen as $dokumen)
                            <li class="flex flex-wrap items-center justify-between gap-3 py-2">
                                <span class="font-bold">{{ $dokumen->jenis->label() }}</span>
                                <span class="flex items-center gap-3">
                                    <x-dasbor.label :tone="$dokumen->status_verifikasi->tone()">{{ $dokumen->status_verifikasi->label() }}</x-dasbor.label>
                                    <a href="{{ route('dokumen.berkas', $dokumen) }}" target="_blank" rel="noopener" class="tautan">Lihat<span class="sr-only"> {{ $dokumen->jenis->label() }}</span></a>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @else
                @if ($tutor->jadwal->isEmpty())
                    <p class="mt-3">Belum ada jadwal.</p>
                @else
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($tutor->jadwal->sortBy(fn ($jadwal) => $jadwal->hari->isoWeekday().$jadwal->jam) as $jadwal)
                            <li class="rounded-full border border-pensil/25 bg-kertas px-3 py-1 font-bold">{{ $jadwal->label() }}</li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </section>
    @endforeach
</div>
