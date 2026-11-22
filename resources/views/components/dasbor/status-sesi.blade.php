@props(['sesi'])

@use('App\Enums\Kehadiran')
@use('App\Support\Format')

{{-- What the tutor logged, and where the parent's confirmation stands. Text labels, never colour alone. --}}
@if ($sesi->kehadiran)
    <span {{ $attributes->merge(['class' => 'inline-flex flex-wrap items-center gap-2']) }}>
        <x-dasbor.label :tone="$sesi->kehadiran === Kehadiran::Hadir ? 'baik' : 'netral'">{{ $sesi->kehadiran->label() }}</x-dasbor.label>
        @if ($sesi->status_konfirmasi)
            <x-dasbor.label :tone="$sesi->status_konfirmasi->tone()">{{ $sesi->status_konfirmasi->label() }}</x-dasbor.label>
        @endif
    </span>
@endif
