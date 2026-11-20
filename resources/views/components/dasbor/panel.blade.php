@props(['heading' => null, 'headingId' => null])

<section @if ($heading) aria-labelledby="{{ $headingId ??= 'panel-'.\Illuminate\Support\Str::slug($heading) }}" @endif
    {{ $attributes->merge(['class' => 'bg-white px-5 py-5 shadow-[0_1px_0_rgb(0_0_0/0.05),0_14px_30px_-24px_rgb(37_37_43/0.5)] sm:px-6']) }}>
    @if ($heading)
        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 border-b-2 border-pensil pb-2">
            <h2 id="{{ $headingId }}" class="font-display text-[1.3rem] font-extrabold tracking-tight [font-stretch:88%]">{{ $heading }}</h2>
            {{ $aside ?? '' }}
        </div>
    @endif
    {{ $slot }}
</section>
