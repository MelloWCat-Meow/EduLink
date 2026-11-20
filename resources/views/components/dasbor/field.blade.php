@props(['name', 'label', 'hint' => null, 'for' => null])

{{--
    Label, hint and correction around a control passed as the slot. The control
    must use id "isian-{name}" and aria-describedby="isian-{name}-galat" when invalid.
--}}
@php
    $id = $for ?? 'isian-'.$name;
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
@endphp

<div {{ $attributes }}>
    <label for="{{ $id }}" class="block font-bold">{{ $label }}</label>
    @if ($hint)
        <p id="{{ $id }}-petunjuk" class="text-[0.875rem] text-pensil-muda">{{ $hint }}</p>
    @endif
    <div class="mt-1.5">{{ $slot }}</div>
    <x-dasbor.koreksi :name="$errorKey" :id="$id.'-galat'" />
</div>
