@use('App\Support\Format')

<x-dasbor.layout title="Keluhan">
    <x-dasbor.judul title="Keluhan" note="yang baru di atas, lalu yang sedang diproses" />

    <nav aria-label="Saring menurut status" class="mb-5 flex flex-wrap gap-2">
        @foreach ([null, ...\App\Enums\StatusKeluhan::cases()] as $status)
            @php($aktif = $statusDipilih === $status)
            <a href="{{ route('admin.keluhan.index', array_filter(['status' => $status?->value])) }}" @if ($aktif) aria-current="page" @endif
               @class(['inline-flex min-h-10 items-center rounded-full border-2 px-3.5 text-[0.9375rem] font-bold', 'border-tinta bg-tinta text-white' => $aktif, 'border-pensil/25 bg-white hover:border-tinta' => ! $aktif])>{{ $status?->label() ?? 'Semua' }}</a>
        @endforeach
    </nav>

    <x-dasbor.panel>
        @if ($daftarKeluhan->isEmpty())
            <x-dasbor.kosong message="Tidak ada keluhan di sini." />
        @else
            <div class="overflow-x-auto">
                <table class="tabel">
                    <thead><tr><th scope="col">Nomor</th><th scope="col">Masuk</th><th scope="col">Pengaju</th><th scope="col">Jenis</th><th scope="col">Pesanan</th><th scope="col">Status</th></tr></thead>
                    <tbody>
                        @foreach ($daftarKeluhan as $keluhan)
                            <tr>
                                <td><a href="{{ route('admin.keluhan.show', $keluhan) }}" class="tautan angka">{{ $keluhan->id_keluhan }}</a></td>
                                <td class="whitespace-nowrap">{{ Format::tanggalPendek($keluhan->created_at) }}</td>
                                <td>{{ $keluhan->pengaju->nama }}</td>
                                <td>{{ $keluhan->jenis->label() }}</td>
                                <td class="whitespace-nowrap"><a href="{{ route('admin.pesanan.show', $keluhan->id_pesanan) }}" class="tautan angka">{{ $keluhan->id_pesanan }}</a><span class="block text-[0.8125rem] text-pensil-muda">{{ $keluhan->pesanan->tutor->nama }}</span></td>
                                <td><x-dasbor.label :tone="$keluhan->status->tone()">{{ $keluhan->status->label() }}</x-dasbor.label></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $daftarKeluhan->links('components.dasbor.halaman') }}
        @endif
    </x-dasbor.panel>
</x-dasbor.layout>
