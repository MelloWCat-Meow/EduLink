@use('App\Enums\LangkahPendaftaran')
@use('App\Enums\StatusVerifikasiTutor')
@use('App\Support\Format')

@php
    $profil = $tutor->profil;
    $menunggu = $tutor->status_verifikasi === StatusVerifikasiTutor::Menunggu;
    $bagianDipilih = old('bagian', []);
    $dokumenDipilih = old('dokumen', []);
@endphp

<x-dasbor.layout :title="'Verifikasi '.$tutor->nama">
    <a href="{{ route('admin.verifikasi.index') }}" class="tautan mb-4 inline-block">Kembali ke antrean</a>
    <x-dasbor.judul :title="$tutor->nama" :note="$tutor->id_tutor.' · tutor '.($tutor->isInternal() ? 'internal (pengajar bimbel)' : 'eksternal').' · '.($tampilDiPencarian ? 'tampil di pencarian' : 'tidak tampil di pencarian')">
        <x-slot:actions>
            <x-dasbor.label :tone="$tutor->status_verifikasi->tone()">{{ $tutor->status_verifikasi->label() }}</x-dasbor.label>
        </x-slot:actions>
    </x-dasbor.judul>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] lg:items-start">
        <div class="space-y-8">
            <x-dasbor.panel heading="Data diri">
                <dl class="grid gap-x-6 gap-y-3 sm:grid-cols-3">
                    <div><dt class="text-[0.875rem] text-pensil-muda">Nama</dt><dd class="font-bold">{{ $tutor->nama }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-[0.875rem] text-pensil-muda">Email</dt><dd class="font-bold break-all">{{ $tutor->kontak }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Nomor HP</dt><dd class="angka font-bold">{{ $tutor->no_hp ?? '–' }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Dikirim</dt><dd class="font-bold">{{ Format::waktu($tutor->diajukan_pada) }}</dd></div>
                </dl>
            </x-dasbor.panel>

            <x-dasbor.panel heading="Pendidikan dan pengalaman">
                <dl class="space-y-3">
                    <div><dt class="text-[0.875rem] text-pensil-muda">Pendidikan terakhir</dt><dd class="font-bold">{{ $profil?->pendidikanLabel() ?? '–' }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Kompetensi</dt><dd>{{ $profil?->kompetensi ?: '–' }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Pengalaman mengajar</dt><dd>{{ $profil?->pengalaman ?: '–' }}</dd></div>
                </dl>
            </x-dasbor.panel>

            <x-dasbor.panel heading="Mapel, tarif, dan layanan">
                <dl class="grid gap-x-6 gap-y-3 sm:grid-cols-3">
                    <div class="sm:col-span-3">
                        <dt class="text-[0.875rem] text-pensil-muda">Mapel per jenjang</dt>
                        <dd>
                            @forelse ($tutor->mataPelajaran->sortBy('nama')->groupBy('jenjang') as $jenjang => $daftar)
                                <span class="block"><span class="font-bold">{{ $jenjang }}:</span> {{ $daftar->pluck('nama')->join(', ') }}</span>
                            @empty
                                –
                            @endforelse
                        </dd>
                    </div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Tarif</dt><dd class="angka font-bold">{{ $profil?->tarif !== null ? Format::rupiah($profil->tarif).' / jam' : 'Belum diisi' }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Kecamatan</dt><dd class="font-bold">{{ $profil?->lokasi ?: 'Belum diisi' }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Layanan</dt><dd class="font-bold">{{ $profil?->modes() ? $profil->modesLabel() : '–' }}</dd></div>
                    <div class="sm:col-span-3">
                        <dt class="text-[0.875rem] text-pensil-muda">Jadwal mingguan</dt>
                        <dd class="mt-1 flex flex-wrap gap-2">
                            @forelse ($tutor->jadwal->sortBy(fn ($jadwal) => $jadwal->hari->isoWeekday().$jadwal->jam) as $jadwal)
                                <span class="rounded-full border border-pensil/25 bg-kertas px-3 py-0.5 font-bold">{{ $jadwal->label() }}</span>
                            @empty
                                –
                            @endforelse
                        </dd>
                    </div>
                </dl>
            </x-dasbor.panel>

            <x-dasbor.panel heading="Dokumen">
                @if ($tutor->dokumen->isEmpty())
                    <p>{{ $tutor->isInternal() ? 'Tidak ada dokumen baru. Dokumen pengajar bimbel ada di arsip perusahaan.' : 'Tidak ada dokumen.' }}</p>
                @else
                    <ul class="divide-y divide-pensil/12">
                        @foreach ($tutor->dokumen as $dokumen)
                            <li class="flex flex-wrap items-start justify-between gap-3 py-3">
                                <div>
                                    <p class="font-bold">{{ $dokumen->jenis->label() }}</p>
                                    <p class="text-[0.875rem] text-pensil-muda"><span class="angka">{{ strtoupper(pathinfo($dokumen->berkas, PATHINFO_EXTENSION)) }} · {{ Format::ukuranBerkas($ukuranBerkas[$dokumen->id_dokumen] ?? null) }}</span> · diunggah {{ Format::tanggalPendek($dokumen->created_at) }}</p>
                                    @if ($dokumen->alasan_penolakan)
                                        <p class="mt-1 text-[0.9375rem]">Alasan ditolak: {{ $dokumen->alasan_penolakan }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <x-dasbor.label :tone="$dokumen->status_verifikasi->tone()">{{ $dokumen->status_verifikasi->label() }}</x-dasbor.label>
                                    <a href="{{ route('dokumen.berkas', $dokumen) }}" target="_blank" rel="noopener" class="tombol-garis min-h-10">Buka<span class="sr-only"> {{ $dokumen->jenis->label() }}</span></a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-dasbor.panel>

            <x-dasbor.panel heading="Riwayat verifikasi">
                @if ($tutor->riwayatVerifikasi->isEmpty())
                    <x-dasbor.kosong message="Belum ada riwayat." />
                @else
                    <ol class="space-y-4">
                        @foreach ($tutor->riwayatVerifikasi as $riwayat)
                            <li class="border-l-2 border-pensil/25 pl-4">
                                <p class="flex flex-wrap items-center gap-2">
                                    <x-dasbor.label :tone="$riwayat->status->tone()">{{ $riwayat->status === StatusVerifikasiTutor::Menunggu ? 'Dikirim tutor' : $riwayat->status->label() }}</x-dasbor.label>
                                    <span class="text-[0.875rem] text-pensil-muda">{{ Format::waktu($riwayat->dicatat_pada) }} · oleh {{ $riwayat->akun?->nama ?? 'sistem' }}</span>
                                </p>
                                @if ($riwayat->catatan)
                                    <p class="mt-1">{{ $riwayat->catatan }}</p>
                                @endif
                                @if ($riwayat->bagian)
                                    <ul class="mt-1 list-disc pl-5 text-[0.9375rem]">
                                        @foreach ($riwayat->bagian as $kunci => $isi)
                                            <li><span class="font-bold">{{ LangkahPendaftaran::from($kunci)->label() }}</span>{{ $isi ? ': '.$isi : '' }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-dasbor.panel>
        </div>

        <x-dasbor.panel heading="Keputusan">
            @if (! $menunggu)
                <x-dasbor.kosong message="Pendaftaran ini tidak sedang menunggu pemeriksaan." />
            @else
                @if ($kekurangan !== [])
                    <p class="mb-5 border-l-4 border-cap bg-jambu/50 px-3 py-2 text-[0.9375rem]">Catatan sistem: ada bagian yang belum lengkap ({{ collect(array_keys($kekurangan))->map(fn ($k) => LangkahPendaftaran::from($k)->label())->join(', ') }}).</p>
                @endif

                <form method="post" action="{{ route('admin.verifikasi.setujui', $tutor) }}">
                    @csrf
                    <h3 class="font-display text-[1.1rem] font-extrabold">Setujui</h3>
                    <p class="text-[0.9375rem]">Semua dokumen ditandai valid dan profil {{ $tutor->nama }} langsung tampil di pencarian.</p>
                    <button type="submit" class="tombol mt-3">Setujui pendaftaran</button>
                </form>

                <form method="post" action="{{ route('admin.verifikasi.perbaikan', $tutor) }}" class="mt-7 border-t-2 border-dashed border-pensil/25 pt-5" novalidate>
                    @csrf
                    <h3 class="font-display text-[1.1rem] font-extrabold">Minta perbaikan</h3>
                    <p class="text-[0.9375rem]">Tandai bagian atau dokumen yang bermasalah. Tutor bisa memperbaiki lalu mengirim ulang.</p>

                    <fieldset class="mt-4" aria-describedby="@error('bagian') isian-bagian-galat @enderror">
                        <legend class="font-bold">Bagian yang perlu diperbaiki</legend>
                        <div class="mt-2 space-y-3">
                            @foreach (LangkahPendaftaran::bagian() as $bagian)
                                <div>
                                    <label class="flex min-h-11 items-center gap-3">
                                        <input type="checkbox" name="bagian[]" value="{{ $bagian->value }}" class="size-5 accent-tinta" @checked(in_array($bagian->value, $bagianDipilih, true))>
                                        {{ $bagian->label() }}
                                    </label>
                                    <label for="catatan-bagian-{{ $bagian->value }}" class="sr-only">Catatan untuk bagian {{ $bagian->label() }}</label>
                                    <input id="catatan-bagian-{{ $bagian->value }}" name="catatan_bagian[{{ $bagian->value }}]" value="{{ old('catatan_bagian.'.$bagian->value) }}" maxlength="255" placeholder="Catatan untuk bagian ini (opsional)" class="kontrol ml-8 w-[calc(100%-2rem)]">
                                </div>
                            @endforeach
                        </div>
                        <x-dasbor.koreksi name="bagian" />
                    </fieldset>

                    @if ($tutor->dokumen->isNotEmpty())
                        <fieldset class="mt-5">
                            <legend class="font-bold">Dokumen yang ditolak</legend>
                            <div class="mt-2 space-y-3">
                                @foreach ($tutor->dokumen as $dokumen)
                                    <div>
                                        <label class="flex min-h-11 items-center gap-3">
                                            <input type="checkbox" name="dokumen[]" value="{{ $dokumen->id_dokumen }}" class="size-5 accent-tinta" @checked(in_array($dokumen->id_dokumen, $dokumenDipilih, true))>
                                            {{ $dokumen->jenis->label() }} <span class="text-[0.875rem] text-pensil-muda">{{ $dokumen->id_dokumen }}</span>
                                        </label>
                                        <label for="alasan-dokumen-{{ $dokumen->id_dokumen }}" class="sr-only">Alasan {{ $dokumen->jenis->label() }} ditolak</label>
                                        <input id="alasan-dokumen-{{ $dokumen->id_dokumen }}" name="alasan_dokumen[{{ $dokumen->id_dokumen }}]" value="{{ old('alasan_dokumen.'.$dokumen->id_dokumen) }}" maxlength="255" placeholder="Apa yang salah? Wajib kalau dicentang" class="kontrol ml-8 w-[calc(100%-2rem)]"
                                            @error('alasan_dokumen.'.$dokumen->id_dokumen) aria-invalid="true" aria-describedby="alasan-dokumen-{{ $dokumen->id_dokumen }}-galat" @enderror>
                                        <x-dasbor.koreksi :name="'alasan_dokumen.'.$dokumen->id_dokumen" :id="'alasan-dokumen-'.$dokumen->id_dokumen.'-galat'" class="ml-8" />
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    @endif

                    <x-dasbor.field name="catatan" label="Catatan untuk tutor" hint="Dikirim lewat email dan tampil di atas formulir tutor." class="mt-5">
                        <textarea id="isian-catatan" name="catatan" maxlength="1000" class="kontrol" aria-describedby="isian-catatan-petunjuk @error('catatan') isian-catatan-galat @enderror" @error('catatan') aria-invalid="true" @enderror>{{ old('catatan') }}</textarea>
                    </x-dasbor.field>
                    <button type="submit" class="tombol-garis mt-3">Kirim permintaan perbaikan</button>
                </form>

                <form method="post" action="{{ route('admin.verifikasi.tolak', $tutor) }}" class="mt-7 border-t-2 border-dashed border-pensil/25 pt-5" novalidate>
                    @csrf
                    <h3 class="font-display text-[1.1rem] font-extrabold">Tolak</h3>
                    <p class="text-[0.9375rem]">Final: tutor tidak bisa mengajukan ulang.</p>
                    <x-dasbor.field name="alasan" label="Alasan menolak" hint="Wajib. Dikirim ke tutor lewat email." class="mt-3">
                        <textarea id="isian-alasan" name="alasan" maxlength="255" class="kontrol" aria-describedby="isian-alasan-petunjuk @error('alasan') isian-alasan-galat @enderror" @error('alasan') aria-invalid="true" @enderror>{{ old('alasan') }}</textarea>
                    </x-dasbor.field>
                    <button type="submit" class="tombol-garis tombol-bahaya mt-3">Tolak pendaftaran</button>
                </form>
            @endif
        </x-dasbor.panel>
    </div>
</x-dasbor.layout>
