@props(['title', 'note' => null])

{{-- Page title: the one place each page gets the highlighter. --}}
<div {{ $attributes->merge(['class' => 'mb-7 flex flex-wrap items-end justify-between gap-x-6 gap-y-3']) }}>
    <div>
        <h1 class="font-display text-[clamp(1.9rem,4vw,2.6rem)] leading-[1.05] font-extrabold tracking-[-0.02em] [font-stretch:88%]">
            <span class="stabilo">{{ $title }}</span>
        </h1>
        @if ($note)
            <p class="mt-2 font-tangan text-[1.15rem] text-pensil-muda">{{ $note }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap gap-3">{{ $actions }}</div>
    @endisset
</div>
