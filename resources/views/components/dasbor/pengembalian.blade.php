@props(['daftar'])

@use('App\Support\Format')
@use('App\Enums\StatusPengembalian')

{{--
    Refunds of one order: sessions not held when the admin cancelled it, and
    sessions ruled not valid after a dispute. Needs sesi_count on each refund.
--}}
@if ($daftar->isNotEmpty())
    <x-dasbor.panel heading="Pengembalian dana" heading-id="pengembalian" {{ $attributes }}>
        <ul class="space-y-4">
            @foreach ($daftar as $pengembalian)
                <li>
                    <p class="flex flex-wrap items-center gap-3">
                        <span class="angka font-display text-[1.5rem] font-extrabold">{{ Format::rupiah($pengembalian->nominal) }}</span>
                        <x-dasbor.label :tone="$pengembalian->status->tone()">{{ $pengembalian->status->label() }}</x-dasbor.label>
                    </p>
                    <p class="mt-1 text-[0.9375rem]">Untuk {{ $pengembalian->sesi_count }} sesi yang tidak terlaksana atau dinyatakan tidak sah. Sesi yang sudah dikonfirmasi tetap dibayarkan kepada tutor.</p>
                    @if ($pengembalian->status === StatusPengembalian::Selesai)
                        <p class="mt-1 text-[0.9375rem]"><span class="font-bold">Catatan:</span> {{ $pengembalian->catatan }}@if ($pengembalian->diproses_pada) ({{ Format::waktu($pengembalian->diproses_pada) }})@endif</p>
                    @else
                        <p class="mt-1 text-[0.9375rem] text-pensil-muda">Admin EduLink akan mentransfer dana ini. Status berubah setelah transfer dicatat.</p>
                    @endif
                </li>
            @endforeach
        </ul>
    </x-dasbor.panel>
@endif
