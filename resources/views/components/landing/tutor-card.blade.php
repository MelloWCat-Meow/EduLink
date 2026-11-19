@props(['tutor'])

<article class="flex h-full flex-col rounded-lg border border-garis bg-white p-5">
    <div class="flex items-start gap-4">
        {{-- Initials stand in for the tutor's photo. --}}
        <div aria-hidden="true" class="flex size-14 shrink-0 items-center justify-center rounded-full bg-kertas text-lg font-extrabold text-dongker ring-1 ring-garis">
            {{ $tutor['initials'] }}
        </div>
        <div class="min-w-0">
            <h3 class="text-base font-bold leading-snug">{{ $tutor['name'] }}</h3>
            <p class="mt-0.5 text-sm text-abu">{{ implode(', ', $tutor['subjects']) }}</p>
            <div class="mt-2 flex flex-wrap gap-1.5">
                @foreach ($tutor['levels'] as $level)
                    <x-landing.level-tag :level="$level" />
                @endforeach
            </div>
        </div>
    </div>

    <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-3 border-t border-kertas pt-4 text-sm">
        <div>
            <dt class="text-abu">Tarif</dt>
            <dd class="font-bold">Rp{{ number_format($tutor['hourly_rate'], 0, ',', '.') }}<span class="font-normal text-abu">/jam</span></dd>
        </div>
        <div>
            <dt class="text-abu">Rating</dt>
            <dd class="font-bold">
                <span aria-hidden="true" class="text-dongker">★</span>
                {{ number_format($tutor['rating'], 1, ',', '.') }}
                <span class="font-normal text-abu">({{ $tutor['review_count'] }} ulasan)</span>
            </dd>
        </div>
        <div class="col-span-2">
            <dt class="text-abu">Area mengajar</dt>
            <dd class="font-medium">{{ $tutor['area'] }}</dd>
        </div>
        <div class="col-span-2">
            <dt class="text-abu">Mode</dt>
            <dd class="font-medium">{{ implode(' dan ', $tutor['modes']) }}</dd>
        </div>
    </dl>
</article>
