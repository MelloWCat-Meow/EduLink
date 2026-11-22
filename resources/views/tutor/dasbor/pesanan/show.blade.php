@use('App\Support\Format')
@use('App\Enums\ModeBelajar')
@use('App\Enums\Kehadiran')
@use('App\Enums\StatusPesanan')

<x-dasbor.layout :title="'Pesanan '.$pesanan->id_pesanan">
    <a href="{{ route('tutor.pesanan.index') }}" class="tautan mb-4 inline-block">Kembali ke daftar pesanan</a>
    <x-dasbor.judul :title="'Pesanan '.$pesanan->id_pesanan" :note="$pesanan->mapel->label().' untuk '.$pesanan->siswa->nama.' ('.$pesanan->siswa->jenjang.')'">
        <x-slot:actions><x-dasbor.status :status="$pesanan->status" class="text-[1rem]" /></x-slot:actions>
    </x-dasbor.judul>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)] lg:items-start">
        <div class="space-y-8">
            @if ($pesanan->status === StatusPesanan::MenungguKonfirmasiTutor)
                <x-dasbor.panel heading="Jawab permintaan" heading-id="jawab">
                    <x-dasbor.hitung-mundur :batas="$pesanan->ditahan_hingga" />
                    <p class="mt-2 text-[0.9375rem]">Kalau tidak dijawab sampai batas itu, pesanan batal otomatis dan slot kembali tersedia.</p>

                    <div class="mt-5 grid gap-6 sm:grid-cols-2">
                        <form method="post" action="{{ route('tutor.pesanan.terima', $pesanan) }}">
                            @csrf
                            <p class="text-[0.9375rem]">Orang tua lalu punya 24 jam untuk membayar.</p>
                            <button type="submit" class="tombol mt-3">Terima pesanan</button>
                        </form>
                        <form method="post" action="{{ route('tutor.pesanan.tolak', $pesanan) }}" novalidate>
                            @csrf
                            <x-dasbor.field name="alasan" label="Alasan menolak" hint="Wajib. Dikirim ke orang tua.">
                                <textarea id="isian-alasan" name="alasan" maxlength="255" rows="2" class="kontrol" aria-describedby="isian-alasan-petunjuk @error('alasan') isian-alasan-galat @enderror" @error('alasan') aria-invalid="true" @enderror>{{ old('alasan') }}</textarea>
                            </x-dasbor.field>
                            <button type="submit" class="tombol-garis tombol-bahaya mt-3">Tolak pesanan</button>
                        </form>
                    </div>
                </x-dasbor.panel>
            @elseif ($pesanan->status === StatusPesanan::MenungguPembayaran)
                <x-dasbor.panel heading="Menunggu pembayaran" heading-id="jawab">
                    <x-dasbor.hitung-mundur :batas="$pesanan->ditahan_hingga" awalan="Batas bayar orang tua" />
                    <form method="post" action="{{ route('tutor.pesanan.batal', $pesanan) }}" class="mt-5 border-t-2 border-dashed border-pensil/25 pt-4" novalidate>
                        @csrf
                        <p class="text-[0.9375rem]">Berhalangan? Anda masih bisa membatalkan sebelum orang tua membayar. Setelah dibayar, pembatalan harus lewat keluhan ke admin.</p>
                        <x-dasbor.field name="alasan" label="Alasan membatalkan" hint="Wajib. Dikirim ke orang tua." class="mt-3">
                            <textarea id="isian-alasan" name="alasan" maxlength="255" rows="2" class="kontrol" aria-describedby="isian-alasan-petunjuk @error('alasan') isian-alasan-galat @enderror" @error('alasan') aria-invalid="true" @enderror>{{ old('alasan') }}</textarea>
                        </x-dasbor.field>
                        <button type="submit" class="tombol-garis tombol-bahaya mt-3">Batalkan pesanan</button>
                    </form>
                </x-dasbor.panel>
            @endif

            <x-dasbor.panel heading="Rincian dan biaya">
                <x-dasbor.rincian-pesanan :pesanan="$pesanan" untuk-tutor>
                    @if ($pesanan->komisi)
                        <p class="mt-2 flex items-baseline justify-between gap-4 text-[0.9375rem]">
                            <span>Komisi EduLink ({{ rtrim(rtrim($pesanan->komisi->persentase, '0'), '.') }}%)</span>
                            <span class="angka">− {{ Format::rupiah($pesanan->komisi->nominal) }}</span>
                        </p>
                        <p class="flex items-baseline justify-between gap-4 font-bold">
                            <span>Bagian Anda</span>
                            <span class="angka">{{ Format::rupiah(bcsub((string) $pesanan->total_biaya, (string) $pesanan->komisi->nominal, 2)) }}</span>
                        </p>
                    @endif
                </x-dasbor.rincian-pesanan>
                @if ($pesanan->mode === \App\Enums\ModeBelajar::KunjunganRumah)
                    <p class="mt-4 border-l-4 border-tinta bg-langit/60 px-3 py-2 text-[0.9375rem]">Alamat rumah tidak disimpan di EduLink. Setelah pesanan dibayar, koordinasikan alamat dengan orang tua lewat WhatsApp.</p>
                @endif
            </x-dasbor.panel>

            @if ($pesanan->mode === ModeBelajar::Online && $pesanan->status === StatusPesanan::Dibayar)
                <x-dasbor.panel heading="Tautan meeting" heading-id="tautan-meeting">
                    <p class="text-[0.9375rem]">Bagikan tautan Google Meet, Zoom, atau layanan lain yang Anda pakai. Tautan ini berlaku untuk semua sesi, kecuali sesi yang diberi tautan sendiri.</p>
                    <form method="post" action="{{ route('tutor.pesanan.tautan', $pesanan) }}" class="mt-3 flex flex-wrap items-end gap-3" novalidate>
                        @csrf
                        @method('PUT')
                        <x-dasbor.field name="tautan_meeting" label="Tautan meeting pesanan" class="min-w-0 flex-1">
                            <input id="isian-tautan_meeting" type="url" name="tautan_meeting" inputmode="url" maxlength="255" placeholder="https://meet.google.com/abc-defg-hij"
                                value="{{ old('untuk_sesi') ? $pesanan->tautan_meeting : old('tautan_meeting', $pesanan->tautan_meeting) }}" class="kontrol"
                                @error('tautan_meeting') aria-invalid="true" aria-describedby="isian-tautan_meeting-galat" @enderror>
                        </x-dasbor.field>
                        <button type="submit" class="tombol">{{ $pesanan->tautan_meeting ? 'Simpan perubahan' : 'Bagikan tautan' }}</button>
                    </form>
                </x-dasbor.panel>
            @endif

            <x-dasbor.panel heading="Sesi">
                @if ($pesanan->sesi->isEmpty())
                    <x-dasbor.kosong :message="$pesanan->status->isFinal() ? 'Pesanan ini tidak punya sesi.' : 'Jadwal sesi dibuat setelah pembayaran berhasil.'" />
                @else
                    <ol class="divide-y divide-pensil/12">
                        @foreach ($pesanan->sesi as $sesi)
                            <li class="flex flex-wrap items-center justify-between gap-x-6 gap-y-1 py-3">
                                <div>
                                    <p class="font-bold">Pertemuan {{ $loop->iteration }} · {{ Format::waktu($sesi->waktu, true) }}</p>
                                    @if ($sesi->catatan)
                                        <p class="text-[0.9375rem] text-pensil-muda">Catatan: {{ $sesi->catatan }}</p>
                                    @endif
                                </div>
                                @if ($sesi->kehadiran)
                                    <x-dasbor.status-sesi :sesi="$sesi" />
                                @elseif ($pesanan->status === StatusPesanan::Dibayar && $sesi->waktu->isPast())
                                    <a href="{{ route('tutor.sesi.index') }}#sesi-{{ $sesi->id_sesi }}" class="tombol-garis min-h-10">Catat<span class="sr-only"> pertemuan {{ $loop->iteration }}</span></a>
                                @elseif ($pesanan->status === StatusPesanan::Dibayar)
                                    <x-dasbor.tautan-meeting :sesi="$sesi" :pesanan="$pesanan" untuk-tutor />
                                @else
                                    <x-dasbor.label>Belum dimulai</x-dasbor.label>
                                @endif
                                @if ($pesanan->mode === ModeBelajar::Online && $pesanan->status === StatusPesanan::Dibayar && ! $sesi->kehadiran)
                                    <details class="basis-full text-[0.9375rem]" @if ($errors->has('tautan_meeting') && old('untuk_sesi') === $sesi->id_sesi) open @endif>
                                        <summary class="tautan inline-flex min-h-11 list-none items-center">{{ $sesi->tautan_meeting ? 'Ubah tautan khusus sesi ini' : 'Pakai tautan lain untuk sesi ini' }}</summary>
                                        <form method="post" action="{{ route('tutor.sesi.tautan', $sesi) }}" class="mt-1 flex flex-wrap items-end gap-2" novalidate>
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="untuk_sesi" value="{{ $sesi->id_sesi }}">
                                            <label class="min-w-0 flex-1"><span class="block font-bold">Tautan sesi {{ $loop->iteration }}</span>
                                                <input type="url" name="tautan_meeting" value="{{ $sesi->tautan_meeting }}" maxlength="255" inputmode="url" placeholder="https://zoom.us/j/..." class="kontrol">
                                            </label>
                                            <button type="submit" class="tombol-garis">Simpan</button>
                                        </form>
                                        <p class="mt-1 text-pensil-muda">Kosongkan lalu simpan untuk kembali memakai tautan pesanan.</p>
                                    </details>
                                @endif
                                <x-dasbor.jadwal-ulang :sesi="$sesi" :pesanan="$pesanan" class="basis-full" />
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-dasbor.panel>

            @if ($pesanan->ulasan?->ditampilkan)
                <x-dasbor.panel heading="Ulasan orang tua">
                    <p class="font-display text-[1.4rem] font-extrabold">{{ $pesanan->ulasan->nilai }} dari 5</p>
                    @if ($pesanan->ulasan->komentar)
                        <p class="mt-1 font-tangan text-[1.25rem] leading-snug text-tinta">“{{ $pesanan->ulasan->komentar }}”</p>
                    @endif
                </x-dasbor.panel>
            @endif
        </div>

        <div class="space-y-8">
            @include('tutor.dasbor.pesanan._kontak')

            <x-dasbor.pengembalian :daftar="$pesanan->pengembalian" />

            <x-dasbor.panel heading="Riwayat status">
                <ol class="space-y-3">
                    @foreach ($pesanan->riwayat as $riwayat)
                        <li class="border-l-2 border-pensil/25 pl-3">
                            <p class="flex flex-wrap items-center gap-2"><x-dasbor.status :status="$riwayat->status" /><span class="text-[0.8125rem] text-pensil-muda">{{ Format::waktu($riwayat->dicatat_pada) }}</span></p>
                            <p class="mt-1 text-[0.9375rem]">{{ $riwayat->catatan }}</p>
                        </li>
                    @endforeach
                </ol>
            </x-dasbor.panel>

            <x-dasbor.panel heading="Keluhan ke admin" heading-id="keluhan">
                @foreach ($pesanan->keluhan as $keluhan)
                    <div class="mb-4 border-b border-pensil/12 pb-3">
                        <p class="flex flex-wrap items-center gap-2"><span class="font-bold">{{ $keluhan->id_keluhan }}</span><x-dasbor.label :tone="$keluhan->status->tone()">{{ $keluhan->status->label() }}</x-dasbor.label></p>
                        <p class="text-[0.9375rem]">{{ $keluhan->deskripsi }}</p>
                        @if ($keluhan->tanggapan_admin)
                            <p class="mt-1 text-[0.9375rem]"><span class="font-bold">Tanggapan admin:</span> {{ $keluhan->tanggapan_admin }}</p>
                        @endif
                    </div>
                @endforeach

                <form method="post" action="{{ route('tutor.pesanan.keluhan', $pesanan) }}" class="space-y-4" novalidate>
                    @csrf
                    <p class="text-[0.9375rem]">Ada masalah dengan pesanan ini, atau perlu membatalkan pesanan yang sudah dibayar? Sampaikan ke admin.</p>
                    <x-dasbor.field name="jenis" label="Jenis keluhan">
                        <select id="isian-jenis" name="jenis" class="kontrol" @error('jenis') aria-invalid="true" aria-describedby="isian-jenis-galat" @enderror>
                            <option value="">Pilih</option>
                            @foreach ($pilihanKeluhan as $jenis)
                                <option value="{{ $jenis->value }}" @selected(old('jenis') === $jenis->value)>{{ $jenis->label() }}</option>
                            @endforeach
                        </select>
                    </x-dasbor.field>
                    <x-dasbor.field name="deskripsi" label="Ceritakan masalahnya">
                        <textarea id="isian-deskripsi" name="deskripsi" maxlength="2000" class="kontrol" @error('deskripsi') aria-invalid="true" aria-describedby="isian-deskripsi-galat" @enderror>{{ old('deskripsi') }}</textarea>
                    </x-dasbor.field>
                    <button type="submit" class="tombol-garis">Kirim keluhan</button>
                </form>
            </x-dasbor.panel>
        </div>
    </div>
</x-dasbor.layout>
