@props([
    'name',
    'label',
    'type' => 'text',
    'hint' => null,
])

@php
    $id = 'isian-'.$name;
    $hintId = $hint ? $id.'-petunjuk' : null;
    $errorId = $id.'-galat';
    $hasError = $errors->has($name);
    $describedBy = trim(($hasError ? $errorId : '').' '.($hintId ?? ''));
@endphp

<div>
    <label for="{{ $id }}" class="block font-bold">{{ $label }}</label>

    @if ($hint)
        <p id="{{ $hintId }}" class="mt-0.5 text-[0.9375rem] text-pensil-muda">{{ $hint }}</p>
    @endif

    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($name) }}" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($hasError) aria-invalid="true" @endif
        {{ $attributes->merge(['class' => 'isian-teks mt-1']) }}
    >

    @error($name)
        <p id="{{ $errorId }}" class="koreksi">
            <svg aria-hidden="true" class="mt-1 size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round">
                <path d="M4 4.5c4 3.6 8 7.4 12 11.2M15.6 4c-3.8 3.9-7.6 7.9-11.3 12" />
            </svg>
            <span><span class="sr-only">Perlu dibetulkan: </span>{{ $message }}</span>
        </p>
    @enderror
</div>
