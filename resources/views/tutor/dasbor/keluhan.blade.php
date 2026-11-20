@use('App\Support\Format')

<x-dasbor.layout title="Keluhan">
    <x-dasbor.judul title="Keluhan" note="keluhan yang Anda ajukan dan tanggapan admin" />

    <x-dasbor.panel>
        @if ($daftarKeluhan->isEmpty())
            <x-dasbor.kosong message="Belum ada keluhan. Ajukan keluhan dari halaman detail pesanan." />
        @else
            <ul class="divide-y divide-pensil/12">
                @foreach ($daftarKeluhan as $keluhan)
                    <li class="py-4">
                        <p class="flex flex-wrap items-center gap-2">
                            <span class="font-bold">{{ $keluhan->id_keluhan }}</span>
                            <x-dasbor.label :tone="$keluhan->status->tone()">{{ $keluhan->status->label() }}</x-dasbor.label>
                            <span class="text-[0.875rem] text-pensil-muda">{{ $keluhan->jenis->label() }} · {{ Format::tanggalPendek($keluhan->created_at) }} · <a href="{{ route('tutor.pesanan.show', $keluhan->id_pesanan) }}" class="tautan">{{ $keluhan->id_pesanan }}</a></span>
                        </p>
                        <p class="mt-1">{{ $keluhan->deskripsi }}</p>
                        <p class="mt-1 text-[0.9375rem]"><span class="font-bold">Tanggapan admin:</span> {{ $keluhan->tanggapan_admin ?: 'belum ada' }}</p>
                    </li>
                @endforeach
            </ul>
            {{ $daftarKeluhan->links('components.dasbor.halaman') }}
        @endif
    </x-dasbor.panel>
</x-dasbor.layout>
