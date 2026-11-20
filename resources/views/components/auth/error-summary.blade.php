{{-- Announced once on load so screen reader users know the sheet came back with corrections. --}}
@if ($errors->any())
    <p role="alert" class="mb-6 flex items-start gap-2 border-l-0 bg-[rgb(192_38_45/0.06)] px-3 py-2 font-tangan text-[1.2rem] leading-snug text-cap">
        <svg aria-hidden="true" class="mt-1 size-5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
            <path d="M10 3.5c.2 3.3.1 6.5-.1 9.4M10 16.4v.2" />
        </svg>
        @php($fieldCount = count($errors->keys()))
        <span>
            @if ($fieldCount === 1)
                Ada satu isian yang perlu dibetulkan.
            @else
                Ada {{ $fieldCount }} isian yang perlu dibetulkan.
            @endif
        </span>
    </p>
@endif
