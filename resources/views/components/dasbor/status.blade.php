@props(['status'])

{{-- Order status as a label: text and a shape, never colour alone. --}}
@use('App\Enums\StatusPesanan', 'S')

@php
    [$warna, $ikon] = match ($status) {
        S::MenungguKonfirmasiTutor, S::MenungguPembayaran => ['bg-sticky border-pensil/30', 'jam'],
        S::Dibayar => ['bg-langit border-tinta/30', 'koin'],
        S::Selesai => ['bg-mint border-pensil/25', 'centang'],
        S::Ditolak, S::Batal => ['bg-jambu/70 border-cap/30', 'silang'],
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[0.875rem] font-bold whitespace-nowrap $warna"]) }}>
    <svg aria-hidden="true" class="size-3.5 shrink-0" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        @switch($ikon)
            @case('jam') <circle cx="8" cy="8" r="6"/><path d="M8 4.5V8l2.5 1.5"/> @break
            @case('koin') <rect x="2" y="4" width="12" height="8" rx="1.5"/><path d="M2 7h12"/> @break
            @case('centang') <path d="M3 8.5l3.2 3L13 4.5"/> @break
            @default <path d="M4 4l8 8M12 4l-8 8"/>
        @endswitch
    </svg>
    <span>{{ $status->label() }}</span>
</span>
