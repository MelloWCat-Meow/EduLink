@use('App\Support\Format')
@use('App\Enums\StatusPengembalian')

<x-dasbor.layout title="Pengembalian dana">
    <x-dasbor.judul title="Pengembalian dana" note="dibuat otomatis saat admin membatalkan pesanan yang sudah dibayar; transfer manual, lalu tandai selesai" />

    <nav aria-label="Saring menurut status" class="mb-5 flex flex-wrap gap-2">
        @foreach ([null => 'Semua', 'perlu_diproses' => 'Perlu diproses', 'selesai' => 'Sudah dikembalikan'] as $nilai => $label)
            @php($aktif = ($statusDipilih?->value) === ($nilai ?: null))
            <a href="{{ route('admin.pengembalian.index', $nilai ? ['status' => $nilai] : []) }}" @if ($aktif) aria-current="page" @endif
               @class(['tombol-garis min-h-10', 'bg-langit' => $aktif])>{{ $label }}</a>
        @endforeach
    </nav>

    <x-dasbor.panel>
        @if ($daftar->isEmpty())
            <x-dasbor.kosong message="Tidak ada pengembalian dana." />
        @else
            <ul class="divide-y divide-pensil/12">
                @foreach ($daftar as $pengembalian)
                    @php($pesanan = $pengembalian->pesanan)
                    <li class="grid grid-cols-[minmax(0,1fr)] gap-4 py-4 lg:grid-cols-[minmax(0,1fr)_22rem]">
                        <div>
                            <p class="flex flex-wrap items-center gap-2">
                                <span class="angka font-bold">{{ $pengembalian->id_pengembalian }}</span>
                                <x-dasbor.label :tone="$pengembalian->status->tone()">{{ $pengembalian->status->label() }}</x-dasbor.label>
                                <span class="text-[0.875rem] text-pensil-muda">dicatat {{ Format::waktu($pengembalian->created_at) }}</span>
                            </p>
                            <p class="angka mt-1 font-display text-[1.5rem] font-extrabold">{{ Format::rupiah($pengembalian->nominal) }}</p>
                            <p class="text-[0.9375rem]">
                                <a href="{{ route('admin.pesanan.show', $pesanan) }}" class="tautan angka">{{ $pesanan->id_pesanan }}</a>
                                · {{ $pesanan->mapel->label() }} · tutor {{ $pesanan->tutor->nama }}
                            </p>
                            <p class="text-[0.9375rem]">Untuk {{ $pesanan->siswa->wali->nama }} ({{ $pesanan->siswa->wali->kontak }}{{ $pesanan->siswa->wali->no_hp ? ', '.$pesanan->siswa->wali->no_hp : '' }})</p>
                            <p class="text-[0.875rem] text-pensil-muda">{{ $pengembalian->sesi_count }} dari {{ $pesanan->jumlah_pertemuan }} sesi dikembalikan (tidak terlaksana atau tidak sah)</p>
                            @if ($pengembalian->status === StatusPengembalian::Selesai)
                                <p class="mt-1 text-[0.9375rem]"><span class="font-bold">Catatan:</span> {{ $pengembalian->catatan }}@if ($pengembalian->pemroses) · {{ $pengembalian->pemroses->nama }}, {{ Format::waktu($pengembalian->diproses_pada) }}@endif</p>
                            @endif
                        </div>
                        @if ($pengembalian->status === StatusPengembalian::PerluDiproses)
                            <form method="post" action="{{ route('admin.pengembalian.selesai', $pengembalian) }}" class="space-y-2" novalidate>
                                @csrf
                                <label for="catatan-{{ $pengembalian->id_pengembalian }}" class="block font-bold">Catatan transfer</label>
                                <input id="catatan-{{ $pengembalian->id_pengembalian }}" name="catatan" maxlength="255" class="kontrol" placeholder="Ditransfer ke BCA a.n. ... tanggal ...">
                                <button type="submit" class="tombol">Tandai selesai<span class="sr-only"> {{ $pengembalian->id_pengembalian }}</span></button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
            {{ $daftar->links('components.dasbor.halaman') }}
        @endif
    </x-dasbor.panel>
</x-dasbor.layout>
