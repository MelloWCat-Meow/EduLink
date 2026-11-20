@props(['rata' => null, 'jumlah' => 0])

@if ($jumlah > 0)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1']) }}>
        <svg aria-hidden="true" class="size-4 text-tinta" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.8l2.5 5.3 5.8.7-4.3 4 1.1 5.8L10 14.8l-5.1 2.8 1.1-5.8-4.3-4 5.8-.7z"/></svg>
        <span class="font-bold">{{ number_format((float) $rata, 1, ',', '') }}</span>
        <span class="text-pensil-muda">dari 5 · {{ $jumlah }} ulasan</span>
    </span>
@else
    <span {{ $attributes->merge(['class' => 'text-pensil-muda']) }}>Belum ada ulasan</span>
@endif
