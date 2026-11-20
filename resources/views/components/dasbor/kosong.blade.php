@props(['message'])

{{-- Empty list: a handwritten note with the next step. --}}
<div {{ $attributes->merge(['class' => 'py-6 text-center']) }}>
    <p class="font-tangan text-[1.35rem] leading-snug text-pensil-muda">{{ $message }}</p>
    @if (! $slot->isEmpty())
        <div class="mt-4 flex justify-center">{{ $slot }}</div>
    @endif
</div>
