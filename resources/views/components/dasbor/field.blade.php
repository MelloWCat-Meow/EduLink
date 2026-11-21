@props(['name', 'label', 'hint' => null, 'for' => null, 'sandi' => false, 'bag' => 'default'])

{{--
    Label, hint and correction around a control passed as the slot. The control
    must use id "isian-{name}" and aria-describedby="isian-{name}-galat" when invalid.
    With "sandi", the slot is a password input and gets the show/hide toggle;
    "bag" picks the error bag the correction is read from.
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
    @if ($sandi)
        <div class="kolom-sandi relative mt-1.5">
            {{ $slot }}
            <x-tombol-sandi :untuk="$id" />
        </div>
    @else
        <div class="mt-1.5">{{ $slot }}</div>
    @endif
    <x-dasbor.koreksi :name="$errorKey" :id="$id.'-galat'" :bag="$bag" />
</div>
