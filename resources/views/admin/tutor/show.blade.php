@use('App\Support\Format')
@use('App\Enums\StatusVerifikasiTutor')

@php
    $profil = $tutor->profil;
    $dipilih = old('mapel', $tutor->mataPelajaran->pluck('kode_mapel')->all());
@endphp

<x-dasbor.layout :title="'Kelola '.$tutor->nama">
    <a href="{{ route('admin.tutor.index') }}" class="tautan mb-4 inline-block">Kembali ke daftar tutor</a>
    <x-dasbor.judul :title="$tutor->nama" :note="$tutor->id_tutor.' · '.($tutor->isInternal() ? 'internal' : 'eksternal').' · '.($tampilDiPencarian ? 'tampil di pencarian' : 'tidak tampil di pencarian')">
        <x-slot:actions>
            <x-dasbor.label :tone="$tutor->status_verifikasi->tone()">{{ $tutor->status_verifikasi->label() }}</x-dasbor.label>
            @if ($tutor->status_verifikasi === StatusVerifikasiTutor::Menunggu)
                <a href="{{ route('admin.verifikasi.show', $tutor) }}" class="tombol-garis">Periksa pendaftaran</a>
            @endif
            @if ($tampilDiPencarian)
                <a href="{{ route('tutor.show', $tutor) }}" class="tombol-garis">Profil publik</a>
            @endif
        </x-slot:actions>
    </x-dasbor.judul>

    <dl class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Pesanan', $rating->pesanan_count],
            ['Rating', $rating->jumlah_ulasan > 0 ? number_format((float) $rating->rata_rating, 1, ',', '').' dari 5 ('.$rating->jumlah_ulasan.')' : 'belum ada'],
            ['Saldo', Format::rupiah($saldo['saldo'])],
            ['Tarif', $profil?->tarif !== null ? Format::rupiah($profil->tarif).' / jam' : 'belum diisi'],
        ] as [$label, $nilai])
            <div class="bg-white px-5 py-4 shadow-[0_1px_0_rgb(0_0_0/0.05),0_14px_30px_-24px_rgb(37_37_43/0.5)]">
                <dt class="text-[0.9375rem] font-bold text-pensil-muda">{{ $label }}</dt>
                <dd class="angka mt-1 font-display text-[1.5rem] leading-tight font-extrabold">{{ $nilai }}</dd>
            </div>
        @endforeach
    </dl>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] lg:items-start">
        <div class="space-y-8">
            <x-dasbor.panel heading="Profil">
                <dl class="grid gap-x-6 gap-y-3 sm:grid-cols-2">
                    <div><dt class="text-[0.875rem] text-pensil-muda">Email</dt><dd class="font-bold break-all">{{ $tutor->kontak }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Nomor HP</dt><dd class="angka font-bold">{{ $tutor->no_hp ?? '–' }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Kecamatan</dt><dd class="font-bold">{{ $profil?->lokasi ?: '–' }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Layanan</dt><dd class="font-bold">{{ $profil?->modes() ? $profil->modesLabel() : '–' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-[0.875rem] text-pensil-muda">Pendidikan</dt><dd>{{ $profil?->pendidikanLabel() ?? '–' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-[0.875rem] text-pensil-muda">Kompetensi</dt><dd>{{ $profil?->kompetensi ?: '–' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-[0.875rem] text-pensil-muda">Pengalaman</dt><dd>{{ $profil?->pengalaman ?: '–' }}</dd></div>
                </dl>
            </x-dasbor.panel>

            <x-dasbor.panel heading="Mata pelajaran dan jenjang" heading-id="mapel">
                <form method="post" action="{{ route('admin.tutor.mapel', $tutor) }}" class="space-y-5" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="grid gap-5 sm:grid-cols-3">
                        @foreach ($mapelPerJenjang as $jenjang => $daftar)
                            <fieldset>
                                <legend class="font-bold">{{ $jenjang }}</legend>
                                @foreach ($daftar as $mapel)
                                    <label class="flex min-h-11 items-center gap-2">
                                        <input type="checkbox" name="mapel[]" value="{{ $mapel->kode_mapel }}" class="size-5 accent-tinta" @checked(in_array($mapel->kode_mapel, $dipilih, true))>
                                        {{ $mapel->nama }}
                                    </label>
                                @endforeach
                            </fieldset>
                        @endforeach
                    </div>
                    <x-dasbor.koreksi name="mapel" />
                    <x-dasbor.field name="alasan_mapel" for="isian-alasan-mapel" label="Alasan perubahan" hint="Wajib. Dicatat di riwayat dan dikirim ke tutor. Mapel yang masih dipakai pesanan berjalan tidak bisa dihapus.">
                        <input id="isian-alasan-mapel" name="alasan" maxlength="255" value="{{ old('alasan') }}" class="kontrol" aria-describedby="isian-alasan-mapel-petunjuk">
                    </x-dasbor.field>
                    <x-dasbor.koreksi name="alasan" id="isian-alasan-mapel-galat" />
                    <button type="submit" class="tombol">Simpan mata pelajaran</button>
                </form>
            </x-dasbor.panel>

            <x-dasbor.panel heading="Riwayat verifikasi dan perubahan">
                @if ($tutor->riwayatVerifikasi->isEmpty())
                    <x-dasbor.kosong message="Belum ada riwayat." />
                @else
                    <ol class="space-y-3">
                        @foreach ($tutor->riwayatVerifikasi as $riwayat)
                            <li class="border-l-2 border-pensil/25 pl-3">
                                <p class="flex flex-wrap items-center gap-2"><x-dasbor.label :tone="$riwayat->status->tone()">{{ $riwayat->status->label() }}</x-dasbor.label><span class="text-[0.875rem] text-pensil-muda">{{ Format::waktu($riwayat->dicatat_pada) }} · {{ $riwayat->akun?->nama ?? 'sistem' }}</span></p>
                                @if ($riwayat->catatan)<p class="mt-1 text-[0.9375rem]">{{ $riwayat->catatan }}</p>@endif
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-dasbor.panel>
        </div>

        @if ($tutor->akun)
            <x-dasbor.status-akun :akun="$tutor->akun" :pesanan-dibayar="$pesananDibayar" />
        @else
            <x-dasbor.panel heading="Status akun">
                <p>Tutor ini belum mengaktifkan akun, jadi belum bisa masuk dan tidak ada akun yang bisa dinonaktifkan.</p>
            </x-dasbor.panel>
        @endif
    </div>
</x-dasbor.layout>
