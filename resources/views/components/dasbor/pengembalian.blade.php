@props(['pengembalian', 'pesanan'])

@use('App\Support\Format')
@use('App\Enums\StatusPengembalian')

{{-- Refund status of a cancelled paid order. --}}
@if ($pengembalian)
    <x-dasbor.panel heading="Pengembalian dana" heading-id="pengembalian" {{ $attributes }}>
        <p class="flex flex-wrap items-center gap-3">
            <span class="angka font-display text-[1.6rem] font-extrabold">{{ Format::rupiah($pengembalian->nominal) }}</span>
            <x-dasbor.label :tone="$pengembalian->status->tone()">{{ $pengembalian->status->label() }}</x-dasbor.label>
        </p>
        <p class="mt-1 text-[0.9375rem]">Untuk {{ $pesanan->jumlah_pertemuan - $pengembalian->sesi_terlaksana }} dari {{ $pesanan->jumlah_pertemuan }} sesi yang belum terlaksana. Sesi yang sudah terlaksana tetap dibayarkan kepada tutor.</p>
        @if ($pengembalian->status === StatusPengembalian::Selesai)
            <p class="mt-2 text-[0.9375rem]"><span class="font-bold">Catatan:</span> {{ $pengembalian->catatan }}@if ($pengembalian->diproses_pada) ({{ Format::waktu($pengembalian->diproses_pada) }})@endif</p>
        @else
            <p class="mt-2 text-[0.9375rem] text-pensil-muda">Admin EduLink akan mentransfer dana ini. Status berubah setelah transfer dicatat.</p>
        @endif
    </x-dasbor.panel>
@endif
