@props(['tutors', 'levels'])

@php
    $photoColors = ['bg-sticky', 'bg-langit', 'bg-mint', 'bg-jambu'];
    $tilts = ['-rotate-2', 'rotate-[1.5deg]', '-rotate-1', 'rotate-[2.5deg]'];
    $offsets = ['lg:mt-0', 'lg:mt-14', 'lg:mt-4', 'lg:mt-20'];
@endphp

<section id="contoh-tutor" aria-labelledby="judul-contoh-tutor" class="halaman relative overflow-x-clip pt-20 pb-20 lg:pt-28 lg:pb-28">
    <div class="mx-auto max-w-[78rem]">
        <div class="flex flex-wrap items-end gap-x-8 gap-y-3">
            <h2 id="judul-contoh-tutor" class="max-w-[18ch] font-display text-[clamp(2rem,5vw,3.6rem)] leading-[1.02] font-extrabold tracking-tight [font-stretch:88%]">
                Tarif, jadwal, dan ulasan ada di depan
            </h2>
            <p class="relative -rotate-3 bg-sticky px-4 py-2 font-tangan text-[1.2rem] text-pensil shadow-[0_8px_16px_-12px_rgb(0_0_0/0.5)]">
                Contoh tampilan. Bukan tutor asli.
            </p>
        </div>

        <p class="mt-5 max-w-[36rem] text-[1.0625rem] leading-[1.75rem]">
            Tiap profil memuat tarif per jam, wilayah yang dijangkau, dan cara belajar yang dilayani, jadi Anda bisa membandingkan sebelum memesan.
        </p>

        <p aria-hidden="true" class="mt-6 flex items-center gap-2 font-tangan text-[1.2rem] text-tinta sm:hidden">
            geser ke samping
            <svg class="h-5 w-10" viewBox="0 0 40 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 11c10-4 20-4 34-1" />
                <path d="M29 4l7 6-7 6" />
            </svg>
        </p>

        {{-- Phones: one swipeable row with the next card peeking in. Larger screens: a grid. --}}
        <ul
            tabindex="0"
            aria-label="Contoh profil tutor"
            class="geser -mr-4 flex gap-5 overflow-x-auto pt-6 pr-4 pb-8 pl-1 sm:mr-0 sm:grid sm:grid-cols-2 sm:gap-x-8 sm:gap-y-16 sm:overflow-visible sm:pt-16 sm:pr-0 sm:pl-0 lg:grid-cols-4"
        >
            @foreach ($tutors as $tutor)
                <li class="{{ $offsets[$loop->index % 4] }} w-[78%] max-w-[19rem] shrink-0 pt-3 sm:w-auto sm:max-w-none sm:pt-0">
                    <x-landing.tutor-polaroid
                        :tutor="$tutor"
                        :levels="$levels"
                        :photo-color="$photoColors[$loop->index % 4]"
                        :tilt="$tilts[$loop->index % 4]"
                    />
                </li>
            @endforeach
        </ul>

        <a href="{{ route('tutor.index') }}" class="mt-6 inline-flex min-h-12 items-center rounded-[0.7rem_1.1rem_0.6rem_1.2rem] border-2 border-tinta px-6 font-bold text-tinta hover:bg-langit lg:mt-12">
            Lihat semua tutor
        </a>
    </div>
</section>
