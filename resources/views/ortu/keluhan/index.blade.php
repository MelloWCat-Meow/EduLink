@use('App\Support\Format')

<x-dasbor.layout title="Keluhan">
    <x-dasbor.judul title="Keluhan" note="ajukan keluhan dari halaman detail pesanan" />

    <x-dasbor.panel>
        @if ($daftarKeluhan->isEmpty())
            <x-dasbor.kosong message="Belum ada keluhan. Semoga tetap begitu." />
        @else
            <ul class="divide-y divide-pensil/12">
                @foreach ($daftarKeluhan as $keluhan)
                    <li class="py-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="font-bold">{{ $keluhan->jenis->label() }}</p>
                            <x-dasbor.label :tone="$keluhan->status->tone()">{{ $keluhan->status->label() }}</x-dasbor.label>
                        </div>
                        <p class="text-[0.875rem] text-pensil-muda">
                            {{ $keluhan->id_keluhan }} · {{ Format::tanggal($keluhan->created_at) }} ·
                            <a href="{{ route('pesanan.show', $keluhan->id_pesanan) }}" class="tautan">{{ $keluhan->id_pesanan }}</a>
                            ({{ $keluhan->pesanan->mapel->label() }}, {{ $keluhan->pesanan->tutor->nama }})
                        </p>
                        <p class="mt-2">{{ $keluhan->deskripsi }}</p>
                        @if ($keluhan->tanggapan_admin)
                            <p class="mt-2 bg-langit/50 px-3 py-2"><span class="font-bold">Tanggapan admin:</span> {{ $keluhan->tanggapan_admin }}</p>
                        @else
                            <p class="mt-2 text-[0.9375rem] text-pensil-muda">Belum ada tanggapan.</p>
                        @endif
                    </li>
                @endforeach
            </ul>
            {{ $daftarKeluhan->links('components.dasbor.halaman') }}
        @endif
    </x-dasbor.panel>
</x-dasbor.layout>
