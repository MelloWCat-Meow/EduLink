@props(['title'])

{{--
    Shell for the sign-in, sign-up and account pages: the notebook page from
    the landing, a short heading column and a loose sheet holding the form.
--}}
<x-layouts.app :title="$title">
    <header class="halaman pt-4">
        <nav aria-label="Utama" class="mx-auto flex max-w-[78rem] items-center justify-between gap-4">
            <a href="{{ route('landing') }}" class="font-display text-[1.45rem] leading-none font-extrabold tracking-tight [font-stretch:85%]">
                EduLink <span class="font-tangan text-[1.35rem] font-bold text-tinta">tutor</span>
            </a>
            {{ $nav ?? '' }}
        </nav>
    </header>

    <main id="konten" class="halaman pt-8 pb-20 lg:pt-16 lg:pb-28">
        <div class="mx-auto grid max-w-[78rem] gap-10 lg:grid-cols-12 lg:gap-8">
            <div class="lg:col-span-5 lg:pt-6">
                {{ $aside }}
            </div>

            <div class="lg:col-span-6 lg:col-start-7">
                {{ $slot }}
            </div>
        </div>
    </main>
</x-layouts.app>
