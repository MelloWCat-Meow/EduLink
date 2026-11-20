@props(['title'])

{{--
    Shell for the tutor's own pages (application form, status, tutor area):
    the same notebook page and brand as the sign-in pages, one wide column.
--}}
<x-layouts.app :title="$title">
    <header class="halaman border-b-2 border-dashed border-pensil/15 py-4">
        <nav aria-label="Utama" class="mx-auto flex max-w-[64rem] flex-wrap items-center justify-between gap-x-4 gap-y-2">
            <a href="{{ route('landing') }}" class="font-display text-[1.45rem] leading-none font-extrabold tracking-tight [font-stretch:85%]">
                EduLink <span class="font-tangan text-[1.35rem] font-bold text-tinta">tutor</span>
            </a>
            @auth
                <div class="flex items-center gap-3">
                    <p class="hidden text-[0.9375rem] sm:block"><span class="font-bold">{{ auth()->user()->nama }}</span> · {{ auth()->user()->id_akun }}</p>
                    <x-auth.sign-out />
                </div>
            @else
                <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center font-bold underline decoration-2 underline-offset-4">Sudah punya akun? Masuk</a>
            @endauth
        </nav>
    </header>

    <main id="konten" class="halaman pt-7 pb-20 lg:pt-10">
        <div class="mx-auto max-w-[64rem]">
            <x-dasbor.flash />
            {{ $slot }}
        </div>
    </main>
</x-layouts.app>
