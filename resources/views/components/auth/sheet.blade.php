@props(['heading', 'note' => null, 'headingId' => 'judul-lembar'])

{{-- A loose sheet torn from a pad, same as the landing's "Lembar cari guru". --}}
<section aria-labelledby="{{ $headingId }}" {{ $attributes->merge(['class' => 'relative rotate-[-0.6deg] bg-white px-6 pt-6 pb-8 shadow-[0_1px_0_rgb(0_0_0/0.04),0_22px_40px_-28px_rgb(37_37_43/0.55)] sm:px-10 sm:pt-7']) }}>
    <div aria-hidden="true" class="absolute inset-x-0 -top-2 h-2 bg-white [clip-path:polygon(0_100%,3%_30%,7%_80%,12%_20%,18%_70%,24%_10%,31%_60%,37%_25%,44%_85%,51%_30%,58%_75%,64%_15%,71%_65%,78%_20%,85%_80%,91%_35%,96%_70%,100%_25%,100%_100%)]"></div>

    <div class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1 border-b-2 border-pensil pb-3">
        <h2 id="{{ $headingId }}" class="font-display text-[1.6rem] font-extrabold tracking-tight [font-stretch:85%]">{{ $heading }}</h2>
        @if ($note)
            <p class="font-tangan text-[1.1rem] text-pensil-muda">{{ $note }}</p>
        @endif
    </div>

    <div class="mt-6">
        {{ $slot }}
    </div>
</section>
