@use('App\Support\Format')
@use('App\Enums\StatusPesanan')

<x-dasbor.layout :title="'Pesanan '.$pesanan->id_pesanan">
    <x-dasbor.judul :title="'Pesanan '.$pesanan->id_pesanan" :note="$pesanan->mapel->label().' untuk '.$pesanan->siswa->nama">
        <x-slot:actions><x-dasbor.status :status="$pesanan->status" class="text-[1rem]" /></x-slot:actions>
    </x-dasbor.judul>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)] lg:items-start">
        <div class="space-y-8">
            <x-dasbor.panel heading="Rincian dan biaya">
                <x-dasbor.rincian-pesanan :pesanan="$pesanan" />
            </x-dasbor.panel>

            @if ($pesanan->status === StatusPesanan::MenungguPembayaran)
                <x-dasbor.panel heading="Bayar pesanan" heading-id="bayar">
                    <p>Batas bayar <span class="font-bold">{{ Format::sisaWaktu($pesanan->ditahan_hingga) }}</span>, yaitu {{ Format::waktu($pesanan->ditahan_hingga, true) }}. Lewat dari itu, pesanan batal dan jadwal dilepas.</p>
                    <form method="post" action="{{ route('pesanan.bayar', $pesanan) }}" class="mt-4">
                        @csrf
                        <fieldset @error('metode') aria-describedby="isian-metode-galat" @enderror>
                            <legend class="font-bold">Cara bayar</legend>
                            <div class="mt-2 grid gap-2 sm:grid-cols-3">
                                @foreach ($pilihanMetode as $metode)
                                    <label class="flex min-h-12 items-center gap-2 rounded-md border-2 border-pensil/20 px-3 has-[:checked]:border-tinta has-[:checked]:bg-langit/50">
                                        <input type="radio" name="metode" value="{{ $metode->value }}" @checked(old('metode', 'virtual_account') === $metode->value) class="size-4 accent-tinta">
                                        {{ $metode->label() }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                        <x-dasbor.koreksi name="metode" />
                        <button type="submit" class="tombol mt-4">Bayar {{ Format::rupiah($pesanan->total_biaya) }}</button>
                    </form>
                </x-dasbor.panel>
            @endif

            <x-dasbor.panel heading="Jadwal sesi">
                @if ($pesanan->sesi->isEmpty())
                    <x-dasbor.kosong :message="$pesanan->status->isFinal() ? 'Pesanan ini tidak punya sesi.' : 'Jadwal sesi dibuat setelah pembayaran berhasil.'" />
                @else
                    <ol class="divide-y divide-pensil/12">
                        @foreach ($pesanan->sesi as $sesi)
                            <li class="flex flex-wrap items-center justify-between gap-x-6 gap-y-1 py-3">
                                <div>
                                    <p class="font-bold">Pertemuan {{ $loop->iteration }} · {{ Format::waktu($sesi->waktu, true) }}</p>
                                    @if ($sesi->catatan)
                                        <p class="text-[0.9375rem] text-pensil-muda">Catatan tutor: {{ $sesi->catatan }}</p>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-3">
                                    @if ($sesi->kehadiran)
                                        <x-dasbor.label :tone="$sesi->kehadiran === \App\Enums\Kehadiran::Hadir ? 'baik' : 'buruk'">{{ $sesi->kehadiran->label() }}</x-dasbor.label>
                                    @elseif ($sesi->tautan_meeting && $pesanan->status === StatusPesanan::Dibayar)
                                        <a href="{{ $sesi->tautan_meeting }}" class="tautan" target="_blank" rel="noopener">Ruang meeting<span class="sr-only"> pertemuan {{ $loop->iteration }}</span></a>
                                    @else
                                        <x-dasbor.label>Belum dicatat</x-dasbor.label>
                                    @endif
                                </div>
                                <x-dasbor.jadwal-ulang :sesi="$sesi" :pesanan="$pesanan" class="basis-full" />
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-dasbor.panel>

            @if ($pesanan->status === StatusPesanan::Selesai || $pesanan->ulasan)
                <x-dasbor.panel heading="Ulasan" heading-id="ulasan">
                    @if ($pesanan->ulasan)
                        <p class="font-display text-[1.4rem] font-extrabold">{{ $pesanan->ulasan->nilai }} dari 5</p>
                        @if ($pesanan->ulasan->komentar)
                            <p class="mt-1 font-tangan text-[1.25rem] leading-snug text-tinta">“{{ $pesanan->ulasan->komentar }}”</p>
                        @endif
                        @unless ($pesanan->ulasan->ditampilkan)
                            <p class="mt-3 border-l-4 border-cap bg-jambu/50 px-3 py-2 text-[0.9375rem]">Ulasan ini disembunyikan admin dari profil tutor. Alasan: {{ $pesanan->ulasan->alasan_moderasi }}</p>
                        @endunless
                    @else
                        <form method="post" action="{{ route('pesanan.ulasan', $pesanan) }}" class="space-y-5" novalidate>
                            @csrf
                            <fieldset @error('nilai') aria-describedby="isian-nilai-galat" @enderror>
                                <legend class="font-bold">Berapa nilai untuk {{ $pesanan->tutor->nama }}?</legend>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach (range(1, 5) as $nilai)
                                        <label class="flex min-h-12 min-w-12 items-center justify-center gap-1 rounded-md border-2 border-pensil/20 px-3 font-bold has-[:checked]:border-tinta has-[:checked]:bg-stabilo">
                                            <input type="radio" name="nilai" value="{{ $nilai }}" @checked((int) old('nilai') === $nilai) class="sr-only">
                                            {{ $nilai }}<span aria-hidden="true">★</span>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="mt-1 text-[0.875rem] text-pensil-muda">1 kurang, 5 sangat baik.</p>
                            </fieldset>
                            <x-dasbor.koreksi name="nilai" />

                            <x-dasbor.field name="komentar" label="Komentar (boleh dikosongkan)">
                                <textarea id="isian-komentar" name="komentar" maxlength="1000" class="kontrol" @error('komentar') aria-invalid="true" aria-describedby="isian-komentar-galat" @enderror>{{ old('komentar') }}</textarea>
                            </x-dasbor.field>

                            <button type="submit" class="tombol">Kirim ulasan</button>
                            <p class="text-[0.875rem] text-pensil-muda">Ulasan hanya bisa dikirim sekali.</p>
                        </form>
                    @endif
                </x-dasbor.panel>
            @endif

            <x-dasbor.panel heading="Keluhan" heading-id="keluhan">
                @foreach ($pesanan->keluhan as $keluhan)
                    <div class="mb-4 border-b border-pensil/12 pb-4">
                        <p class="flex flex-wrap items-center gap-2 font-bold">{{ $keluhan->jenis->label() }} <x-dasbor.label :tone="$keluhan->status->tone()">{{ $keluhan->status->label() }}</x-dasbor.label></p>
                        <p class="text-[0.9375rem]">{{ $keluhan->deskripsi }}</p>
                        @if ($keluhan->tanggapan_admin)
                            <p class="mt-2 bg-langit/50 px-3 py-2 text-[0.9375rem]"><span class="font-bold">Tanggapan admin:</span> {{ $keluhan->tanggapan_admin }}</p>
                        @endif
                    </div>
                @endforeach

                <details @if ($errors->hasAny(['jenis', 'deskripsi'])) open @endif>
                    <summary class="tombol-garis">Ajukan keluhan</summary>
                    <form method="post" action="{{ route('pesanan.keluhan', $pesanan) }}" class="mt-4 space-y-5" novalidate>
                        @csrf
                        <x-dasbor.field name="jenis" label="Tentang apa?">
                            <select id="isian-jenis" name="jenis" class="kontrol" @error('jenis') aria-invalid="true" aria-describedby="isian-jenis-galat" @enderror>
                                <option value="">Pilih jenis keluhan</option>
                                @foreach ($pilihanKeluhan as $jenis)
                                    <option value="{{ $jenis->value }}" @selected(old('jenis') === $jenis->value)>{{ $jenis->label() }}</option>
                                @endforeach
                            </select>
                        </x-dasbor.field>
                        <x-dasbor.field name="deskripsi" label="Apa yang terjadi?">
                            <textarea id="isian-deskripsi" name="deskripsi" maxlength="2000" class="kontrol" @error('deskripsi') aria-invalid="true" aria-describedby="isian-deskripsi-galat" @enderror>{{ old('deskripsi') }}</textarea>
                        </x-dasbor.field>
                        <button type="submit" class="tombol">Kirim keluhan</button>
                    </form>
                </details>
            </x-dasbor.panel>
        </div>

        <div class="space-y-8">
            <x-dasbor.panel heading="Tutor">
                <p class="font-bold">{{ $pesanan->tutor->nama }}</p>
                <p class="text-[0.9375rem] text-pensil-muda">{{ $pesanan->mapel->label() }} · {{ $pesanan->mode->label() }}</p>
                @if ($pesanan->mode === \App\Enums\ModeBelajar::KunjunganRumah && $kontak['tautan'])
                    <p class="mt-3 text-[0.9375rem]">Alamat rumah tidak disimpan di EduLink. Kirim alamat ke tutor lewat WhatsApp.</p>
                @endif
                <x-dasbor.tombol-whatsapp :kontak="$kontak" class="mt-4" />
            </x-dasbor.panel>

            @if ($pesanan->status->holdsSlot())
                <x-dasbor.panel heading="Batalkan pesanan">
                    <p class="text-[0.9375rem]">Pesanan yang belum dibayar boleh dibatalkan. Jadwal tutor akan dilepas untuk orang tua lain.</p>
                    <form method="post" action="{{ route('pesanan.batal', $pesanan) }}" class="mt-3" onsubmit="return confirm('Batalkan pesanan {{ $pesanan->id_pesanan }}?')">
                        @csrf
                        <button type="submit" class="tombol-garis tombol-bahaya">Batalkan pesanan</button>
                    </form>
                </x-dasbor.panel>
            @endif

            <x-dasbor.pengembalian :pengembalian="$pesanan->pengembalianDana" :pesanan="$pesanan" />

            <x-dasbor.panel heading="Riwayat status">
                <ol class="relative ml-2 border-l-2 border-dashed border-pensil/30">
                    @foreach ($pesanan->riwayat as $riwayat)
                        <li class="relative pb-4 pl-5 last:pb-0">
                            <span aria-hidden="true" class="absolute top-1.5 -left-[7px] size-3 rounded-full border-2 border-pensil bg-white"></span>
                            <p class="text-[0.875rem] text-pensil-muda">{{ Format::waktu($riwayat->dicatat_pada) }}</p>
                            <p class="font-bold">{{ $riwayat->status->label() }}</p>
                            @if ($riwayat->catatan)
                                <p class="text-[0.9375rem]">{{ $riwayat->catatan }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </x-dasbor.panel>

            <x-dasbor.panel heading="Pembayaran">
                @if ($pesanan->pembayaran->isEmpty())
                    <x-dasbor.kosong message="Belum ada pembayaran." />
                @else
                    <ul class="divide-y divide-pensil/12">
                        @foreach ($pesanan->pembayaran as $pembayaran)
                            <li class="flex flex-wrap items-center justify-between gap-2 py-2.5">
                                <div>
                                    <p class="font-bold">{{ $pembayaran->metode->label() }}</p>
                                    <p class="text-[0.875rem] text-pensil-muda">{{ $pembayaran->id_pembayaran }} · {{ Format::waktu($pembayaran->diproses_pada ?? $pembayaran->created_at) }}</p>
                                </div>
                                <x-dasbor.label :tone="$pembayaran->status->tone()">{{ $pembayaran->status->label() }}</x-dasbor.label>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-dasbor.panel>
        </div>
    </div>
</x-dasbor.layout>
