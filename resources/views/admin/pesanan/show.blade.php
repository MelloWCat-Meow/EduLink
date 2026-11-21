@use('App\Support\Format')

<x-dasbor.layout :title="'Pesanan '.$pesanan->id_pesanan">
    <a href="{{ route('admin.pesanan.index') }}" class="tautan mb-4 inline-block">Kembali ke daftar pesanan</a>
    <x-dasbor.judul :title="'Pesanan '.$pesanan->id_pesanan" :note="'dipesan '.$pesanan->siswa->wali->nama.' ('.$pesanan->siswa->wali->kontak.')'">
        <x-slot:actions><x-dasbor.status :status="$pesanan->status" class="text-[1rem]" /></x-slot:actions>
    </x-dasbor.judul>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)] lg:items-start">
        <div class="space-y-8">
            <x-dasbor.panel heading="Rincian dan biaya">
                <x-dasbor.rincian-pesanan :pesanan="$pesanan">
                    @if ($pesanan->komisi)
                        <p class="mt-2 text-[0.9375rem]">Komisi platform {{ rtrim(rtrim($pesanan->komisi->persentase, '0'), '.') }}%: <span class="angka font-bold">{{ Format::rupiah($pesanan->komisi->nominal) }}</span> ({{ $pesanan->komisi->id_komisi }})</p>
                    @endif
                </x-dasbor.rincian-pesanan>
            </x-dasbor.panel>

            <x-dasbor.panel heading="Sesi">
                @if ($pesanan->sesi->isEmpty())
                    <x-dasbor.kosong message="Belum ada sesi. Sesi dibuat saat pembayaran berhasil." />
                @else
                    <div class="overflow-x-auto">
                        <table class="tabel">
                            <thead><tr><th scope="col">Sesi</th><th scope="col">Waktu</th><th scope="col">Kehadiran</th><th scope="col">Catatan</th></tr></thead>
                            <tbody>
                                @foreach ($pesanan->sesi as $sesi)
                                    <tr>
                                        <td class="angka">{{ $sesi->id_sesi }}</td>
                                        <td class="whitespace-nowrap">{{ Format::waktu($sesi->waktu) }}</td>
                                        <td>{{ $sesi->kehadiran?->label() ?? 'Belum dicatat' }}</td>
                                        <td>{{ $sesi->catatan ?? '–' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-dasbor.panel>

            <x-dasbor.panel heading="Pembayaran">
                @if ($pesanan->pembayaran->isEmpty())
                    <x-dasbor.kosong message="Belum ada percobaan pembayaran." />
                @else
                    <div class="overflow-x-auto">
                        <table class="tabel">
                            <thead><tr><th scope="col">Nomor</th><th scope="col">Cara bayar</th><th scope="col" class="text-right">Jumlah</th><th scope="col">Status</th><th scope="col">Diproses</th></tr></thead>
                            <tbody>
                                @foreach ($pesanan->pembayaran as $pembayaran)
                                    <tr>
                                        <td class="angka">{{ $pembayaran->id_pembayaran }}</td>
                                        <td>{{ $pembayaran->metode->label() }}</td>
                                        <td class="angka text-right">{{ Format::rupiah($pembayaran->jumlah) }}</td>
                                        <td><x-dasbor.label :tone="$pembayaran->status->tone()">{{ $pembayaran->status->label() }}</x-dasbor.label></td>
                                        <td class="whitespace-nowrap">{{ Format::waktu($pembayaran->diproses_pada) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-dasbor.panel>

            <x-dasbor.panel heading="Ulasan dan keluhan">
                @if ($pesanan->ulasan)
                    <p><span class="font-bold">{{ $pesanan->ulasan->nilai }} dari 5</span> · {{ $pesanan->ulasan->ditampilkan ? 'ditampilkan' : 'disembunyikan' }}</p>
                    <p class="text-[0.9375rem]">{{ $pesanan->ulasan->komentar }}</p>
                @else
                    <p class="text-pensil-muda">Belum ada ulasan.</p>
                @endif
                <ul class="mt-4 space-y-2">
                    @forelse ($pesanan->keluhan as $keluhan)
                        <li><a href="{{ route('admin.keluhan.show', $keluhan) }}" class="tautan">{{ $keluhan->id_keluhan }}</a> {{ $keluhan->jenis->label() }} · <x-dasbor.label :tone="$keluhan->status->tone()">{{ $keluhan->status->label() }}</x-dasbor.label></li>
                    @empty
                        <li class="text-pensil-muda">Tidak ada keluhan.</li>
                    @endforelse
                </ul>
            </x-dasbor.panel>
        </div>

        <div class="space-y-8">
            <x-dasbor.panel heading="Riwayat status">
                <ol class="relative ml-2 border-l-2 border-dashed border-pensil/30">
                    @foreach ($pesanan->riwayat as $riwayat)
                        <li class="relative pb-4 pl-5 last:pb-0">
                            <span aria-hidden="true" class="absolute top-1.5 -left-[7px] size-3 rounded-full border-2 border-pensil bg-white"></span>
                            <p class="text-[0.875rem] text-pensil-muda">{{ Format::waktu($riwayat->dicatat_pada) }} · {{ $riwayat->akun?->nama ?? 'sistem' }}</p>
                            <p class="font-bold">{{ $riwayat->status->label() }}</p>
                            <p class="text-[0.9375rem]">{{ $riwayat->catatan }}</p>
                        </li>
                    @endforeach
                </ol>
            </x-dasbor.panel>

            <x-dasbor.pengembalian :pengembalian="$pesanan->pengembalianDana" :pesanan="$pesanan" />

            @unless ($pesanan->status->isFinal())
                <x-dasbor.panel heading="Batalkan pesanan">
                    <form method="post" action="{{ route('admin.pesanan.batal', $pesanan) }}" novalidate onsubmit="return confirm('Batalkan pesanan {{ $pesanan->id_pesanan }}?')">
                        @csrf
                        <x-dasbor.field name="alasan" label="Alasan" hint="Tercatat di riwayat dan terlihat oleh orang tua. Untuk pesanan yang sudah dibayar, pengembalian dana sesi yang belum terlaksana dicatat otomatis dan muncul di halaman Pengembalian dana.">
                            <textarea id="isian-alasan" name="alasan" maxlength="255" class="kontrol" aria-describedby="isian-alasan-petunjuk @error('alasan') isian-alasan-galat @enderror" @error('alasan') aria-invalid="true" @enderror>{{ old('alasan') }}</textarea>
                        </x-dasbor.field>
                        <button type="submit" class="tombol-garis tombol-bahaya mt-3">Batalkan pesanan</button>
                    </form>
                </x-dasbor.panel>
            @endunless
        </div>
    </div>
</x-dasbor.layout>
