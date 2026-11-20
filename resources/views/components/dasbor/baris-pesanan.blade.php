@props(['pesanan', 'href'])

{{-- One order in a list. The whole row is one link. --}}
<li class="relative grid gap-x-6 gap-y-1 border-b border-pensil/12 py-3.5 last:border-0 sm:grid-cols-[1fr_auto] sm:items-center">
    <div class="min-w-0">
        <p class="text-[0.875rem] text-pensil-muda"><span class="angka">{{ $pesanan->id_pesanan }}</span> · dibuat {{ \App\Support\Format::tanggalPendek($pesanan->created_at) }}</p>
        <p class="mt-0.5 font-bold">
            <a href="{{ $href }}" class="after:absolute after:inset-0 hover:underline">{{ $pesanan->mapel->label() }} untuk {{ $pesanan->siswa->nama }}</a>
        </p>
        <p class="text-[0.9375rem]">dengan {{ $pesanan->tutor->nama }} · {{ $pesanan->mode->label() }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 sm:flex-col sm:items-end">
        <x-dasbor.status :status="$pesanan->status" />
        <span class="angka font-bold">{{ \App\Support\Format::rupiah($pesanan->total_biaya) }}</span>
    </div>
</li>
