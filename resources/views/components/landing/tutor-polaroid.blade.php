@props(['tutor', 'levels', 'photoColor' => 'bg-sticky', 'tilt' => '-rotate-1'])

<article class="polaroid selotip relative h-full {{ $tilt }} bg-white p-3 pb-5 shadow-[0_1px_2px_rgb(0_0_0/0.08),0_14px_24px_-20px_rgb(0_0_0/0.5)]">
    <div aria-hidden="true" class="{{ $photoColor }} grid aspect-[5/4] place-items-center">
        <span class="font-tangan text-[4.25rem] leading-none font-bold text-tinta">{{ $tutor['initials'] }}</span>
    </div>

    <div class="px-1 pt-4">
        <h3 class="font-display text-[1.35rem] leading-tight font-extrabold [font-stretch:90%]">{{ $tutor['name'] }}</h3>
        <p class="font-tangan text-[1.15rem] leading-snug text-tinta">
            {{ implode(', ', $tutor['subjects']) }} untuk
            {{ collect($tutor['levels'])->map(fn (string $level): string => $levels[$level]['label'])->implode(' dan ') }}
        </p>

        <dl class="mt-3 space-y-1 text-[0.9375rem] leading-snug">
            <div class="flex justify-between gap-3">
                <dt class="text-pensil-muda">Tarif</dt>
                <dd class="font-bold tabular-nums">Rp{{ number_format($tutor['hourly_rate'], 0, ',', '.') }}/jam</dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-pensil-muda">Rating</dt>
                <dd class="font-bold">{{ number_format($tutor['rating'], 1, ',', '') }}/5 <span class="font-normal text-pensil-muda">({{ $tutor['review_count'] }} ulasan)</span></dd>
            </div>
            <div class="flex justify-between gap-3">
                <dt class="text-pensil-muda">Wilayah</dt>
                <dd class="text-right">{{ $tutor['area'] }}</dd>
            </div>
        </dl>

        <p class="mt-3 flex flex-wrap gap-2 text-[0.875rem] font-bold">
            <span class="sr-only">Cara belajar:</span>
            @foreach ($tutor['modes'] as $mode)
                <span class="stabilo">{{ $mode }}</span>
            @endforeach
        </p>
    </div>
</article>
