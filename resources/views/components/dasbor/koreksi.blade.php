@props(['name', 'id' => null])

@error($name)
    <p id="{{ $id ?? 'isian-'.$name.'-galat' }}" class="koreksi">
        <svg aria-hidden="true" class="mt-1 size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"><path d="M4 4.5c4 3.6 8 7.4 12 11.2M15.6 4c-3.8 3.9-7.6 7.9-11.3 12" /></svg>
        <span><span class="sr-only">Perlu dibetulkan: </span>{{ $message }}</span>
    </p>
@enderror
