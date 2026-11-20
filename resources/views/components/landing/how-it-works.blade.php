@props(['steps'])

<section id="cara-kerja" aria-labelledby="judul-cara-kerja" class="halaman pt-8 pb-20 lg:pb-28">
    <div class="mx-auto max-w-[78rem]">
        <div class="flex flex-wrap items-end justify-between gap-x-10 gap-y-3">
            <h2 id="judul-cara-kerja" class="max-w-[17ch] font-display text-[clamp(2rem,5vw,3.6rem)] leading-[1.02] font-extrabold tracking-tight [font-stretch:88%]">
                Dari mencari guru sampai sesi pertama
            </h2>
            <p class="max-w-[24ch] rotate-[-2deg] font-tangan text-[1.3rem] leading-snug text-tinta">
                semuanya di satu tempat, termasuk bayarnya
            </p>
        </div>

        <ol class="mt-12 grid gap-y-4 lg:mt-16 lg:grid-cols-4 lg:gap-x-4">
            @foreach ($steps as $step)
                <li class="relative grid grid-cols-[3.5rem_1fr] gap-x-4 lg:block lg:pr-6">
                    <span aria-hidden="true" class="relative grid size-14 place-items-center font-tangan text-[2rem] leading-none font-bold text-tinta">
                        {{ $loop->iteration }}
                        <svg class="absolute inset-0 size-full text-tinta" viewBox="0 0 60 60" fill="none">
                            <path d="M33 5C17 3 5 14 5 30s12 26 26 25c15-1 25-12 24-26C54 15 43 5 28 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
                        </svg>
                    </span>

                    <div class="pb-6 lg:pt-5 lg:pb-0">
                        <h3 class="font-display text-[1.45rem] leading-tight font-extrabold [font-stretch:90%]">
                            <span class="sr-only">Langkah {{ $loop->iteration }}: </span>{{ $step['title'] }}
                        </h3>
                        <p class="mt-2 max-w-[36rem] text-[1.0625rem] leading-[1.75rem]">{{ $step['body'] }}</p>
                    </div>

                    @unless ($loop->last)
                        {{-- Doodle arrow to the next step: down on phones, across on desktop. --}}
                        <svg aria-hidden="true" class="col-start-1 mx-auto h-10 w-6 text-pensil-muda lg:hidden" viewBox="0 0 24 40" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2c-5 9 5 17-1 34" />
                            <path d="M5 29l6 8 7-7" />
                        </svg>
                        <svg aria-hidden="true" class="absolute top-3 right-[-1.75rem] hidden h-8 w-16 text-pensil-muda lg:block" viewBox="0 0 64 32" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 20c14-14 30-14 54-6" />
                            <path d="M48 6l10 8-11 6" />
                        </svg>
                    @endunless
                </li>
            @endforeach
        </ol>
    </div>
</section>
