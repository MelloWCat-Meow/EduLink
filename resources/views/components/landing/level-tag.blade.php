@props(['level'])

{{-- Level tag colored after the school uniform of each level. --}}
<span {{ $attributes->class([
    'inline-flex items-center rounded-sm px-2 py-0.5 text-xs font-bold text-white',
    'bg-merah' => $level === 'sd',
    'bg-dongker' => $level === 'smp',
    'bg-abu' => $level === 'sma',
]) }}>{{ strtoupper($level) }}</span>
