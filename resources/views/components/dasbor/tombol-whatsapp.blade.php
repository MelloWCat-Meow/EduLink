@props(['kontak'])

{{-- WhatsApp button, or a plain note when the link is not available. Never shows the number. --}}
@if ($kontak['tautan'])
    <a href="{{ $kontak['tautan'] }}" target="_blank" rel="noopener" {{ $attributes->merge(['class' => 'inline-flex min-h-12 items-center gap-2 rounded-[0.55rem_0.8rem_0.5rem_0.85rem] border-2 border-[#0b6b3a] bg-[#e7f6ec] px-4 font-bold text-[#0b4f2c] hover:bg-[#d3efdc]']) }}>
        <svg aria-hidden="true" class="size-5 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.7-1.4.1-.2 0-.3 0-.5l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.2 2.2 2.2 0 0 0 .1-1.3c0-.1-.2-.2-.4-.3z"/></svg>
        {{ $kontak['label'] }}
        <span class="sr-only">(membuka WhatsApp di tab baru)</span>
    </a>
@elseif ($kontak['keterangan'])
    <p {{ $attributes->merge(['class' => 'text-[0.9375rem] text-pensil-muda']) }}>{{ $kontak['keterangan'] }}</p>
@endif
