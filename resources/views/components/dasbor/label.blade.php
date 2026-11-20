@props(['tone' => 'netral'])

{{-- Small text label for non-order statuses (payment, complaint, document). --}}
@php
    $warna = match ($tone) {
        'baik' => 'bg-mint border-pensil/25',
        'tunggu' => 'bg-sticky border-pensil/30',
        'buruk' => 'bg-jambu/70 border-cap/30',
        'info' => 'bg-langit border-tinta/30',
        default => 'bg-map border-pensil/20',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full border px-2.5 py-0.5 text-[0.875rem] font-bold whitespace-nowrap $warna"]) }}>{{ $slot }}</span>
