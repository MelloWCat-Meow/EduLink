@props(['title'])

{{-- Public pages (tutor search and profiles) for guests: the landing's header, then content. --}}
<x-layouts.app :title="$title">
    <header class="halaman pt-4">
        <nav aria-label="Utama" class="mx-auto flex max-w-[78rem] flex-wrap items-center justify-between gap-4">
            <a href="{{ route('landing') }}" class="font-display text-[1.45rem] leading-none font-extrabold tracking-tight [font-stretch:85%]">
                EduLink <span class="font-tangan text-[1.35rem] font-bold text-tinta">tutor</span>
            </a>
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route(auth()->user()->peran->homeRoute()) }}" class="tombol-garis">Beranda saya</a>
                @else
                    <a href="{{ route('login') }}" class="tombol-garis">Masuk</a>
                    <a href="{{ route('register') }}" class="tombol hidden sm:inline-flex">Daftar</a>
                @endauth
            </div>
        </nav>
    </header>

    <main id="konten" class="halaman pt-8 pb-20">
        <div class="mx-auto max-w-[78rem]">
            <x-dasbor.flash />
            {{ $slot }}
        </div>
    </main>
</x-layouts.app>
