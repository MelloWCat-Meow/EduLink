@props(['untuk'])

{{--
    Show/hide toggle for one password input (id = $untuk). Rendered hidden:
    resources/js/kata-sandi.js reveals and wires it, so without JavaScript
    there is no dead button and the field works as a normal password field.
--}}
<button type="button" hidden data-tampilkan-sandi aria-controls="{{ $untuk }}" aria-pressed="false" aria-label="Tampilkan kata sandi"
    class="tombol-sandi absolute top-1/2 right-0 grid size-11 -translate-y-1/2 place-items-center rounded-md text-tinta hover:bg-langit/60">
    {{-- Open eye: the password is hidden, press to show it. --}}
    <svg data-ikon="tampilkan" aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z" />
        <circle cx="12" cy="12" r="3" />
    </svg>
    {{-- Crossed-out eye: the password is shown, press to hide it. --}}
    <svg data-ikon="sembunyikan" hidden aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M10.6 5.1A10.4 10.4 0 0 1 12 5c6.4 0 10 7 10 7a17.6 17.6 0 0 1-2.6 3.5M6.6 6.6C3.7 8.5 2 12 2 12s3.6 7 10 7a9.7 9.7 0 0 0 5.4-1.6" />
        <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
        <path d="M3 3l18 18" />
    </svg>
</button>
