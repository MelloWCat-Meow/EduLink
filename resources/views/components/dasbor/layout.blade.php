@props(['title'])

@php
    $akun = auth()->user();
    $isAdmin = $akun->peran === \App\Enums\Peran::Admin;
    $isTutor = $akun->peran === \App\Enums\Peran::Tutor;
    $namaPeran = $isAdmin ? 'Admin' : ($isTutor ? 'Tutor' : 'Orang tua');

    $menu = $isTutor
        ? [
            ['tutor.area', 'Beranda', 'tutor.area'],
            ['tutor.pesanan.index', 'Pesanan', 'tutor.pesanan.*'],
            ['tutor.sesi.index', 'Sesi', 'tutor.sesi.*'],
            ['tutor.jadwal.index', 'Jadwal', 'tutor.jadwal.*'],
            ['tutor.pendapatan.index', 'Pendapatan', 'tutor.pendapatan.*'],
            ['tutor.ulasan.index', 'Ulasan', 'tutor.ulasan.*'],
            ['tutor.keluhan.index', 'Keluhan', 'tutor.keluhan.*'],
            ['tutor.profil.edit', 'Profil', 'tutor.profil.*'],
        ]
        : ($isAdmin
        ? [
            ['admin.beranda', 'Ringkasan', 'admin.beranda'],
            ['admin.verifikasi.index', 'Verifikasi tutor', 'admin.verifikasi.*'],
            ['admin.pesanan.index', 'Pesanan', 'admin.pesanan.*'],
            ['admin.keluhan.index', 'Keluhan', 'admin.keluhan.*'],
            ['admin.ulasan.index', 'Ulasan', 'admin.ulasan.*'],
            ['admin.komisi.edit', 'Komisi', 'admin.komisi.*'],
            ['admin.pencairan.index', 'Pencairan dana', 'admin.pencairan.*'],
            ['admin.pengguna.index', 'Data pengguna', 'admin.pengguna.*'],
        ]
        : [
            ['beranda', 'Beranda', 'beranda'],
            ['tutor.index', 'Cari tutor', 'tutor.*'],
            ['pesanan.index', 'Pesanan saya', 'pesanan.*'],
            ['anak.index', 'Data anak', 'anak.*'],
            ['pembayaran.index', 'Pembayaran', 'pembayaran.*'],
            ['keluhan.index', 'Keluhan', 'keluhan.*'],
            ['akun.edit', 'Akun saya', 'akun.*'],
        ]);
@endphp

<x-layouts.app :title="$title">
    <div class="lg:flex lg:min-h-screen">
        {{-- Desktop: the notebook's table of contents down the left. --}}
        <aside class="hidden border-r-2 border-dashed border-pensil/20 bg-kertas lg:sticky lg:top-0 lg:flex lg:h-screen lg:w-64 lg:shrink-0 lg:flex-col lg:px-6 lg:py-7">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('landing') }}" class="font-display text-[1.45rem] leading-none font-extrabold tracking-tight [font-stretch:85%]">
                    EduLink <span class="font-tangan text-[1.35rem] font-bold text-tinta">{{ $isAdmin ? 'admin' : ($isTutor ? 'pengajar' : 'tutor') }}</span>
                </a>
                <x-dasbor.lonceng />
            </div>

            <nav aria-label="Menu dasbor" class="mt-9 flex-1">
                <ul class="space-y-1">
                    @foreach ($menu as [$route, $label, $pattern])
                        @php($aktif = request()->routeIs($pattern))
                        <li>
                            <a href="{{ route($route) }}" @if ($aktif) aria-current="page" @endif
                               class="flex min-h-11 items-center rounded-md px-2 font-display text-[1.05rem] font-bold [font-stretch:92%] hover:bg-langit/60">
                                <span @class(['stabilo' => $aktif])>{{ $label }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="border-t-2 border-dashed border-pensil/20 pt-4">
                <p class="font-bold leading-tight">{{ $akun->nama }}</p>
                <p class="text-[0.875rem] text-pensil-muda">{{ $namaPeran }} · {{ $akun->id_akun }}</p>
                <div class="mt-3"><x-auth.sign-out /></div>
            </div>
        </aside>

        {{-- Phones and tablets: a top bar with a labelled menu drawer. --}}
        <header class="relative z-20 border-b-2 border-dashed border-pensil/20 bg-kertas lg:hidden">
            <div class="halaman flex min-h-14 items-center justify-between gap-3">
                <a href="{{ route('landing') }}" class="font-display text-[1.3rem] leading-none font-extrabold tracking-tight [font-stretch:85%]">
                    EduLink <span class="font-tangan text-[1.2rem] font-bold text-tinta">{{ $isAdmin ? 'admin' : ($isTutor ? 'pengajar' : 'tutor') }}</span>
                </a>
                <div class="flex items-center gap-2">
                    <x-dasbor.lonceng />
                    <details class="group">
                        <summary class="list-none">
                            <span class="tombol-garis min-h-11 px-3 text-[0.95rem]">
                                <svg aria-hidden="true" class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M3 5.5h14M3 10h14M3 14.5h14" class="group-open:hidden"/><path d="M5 5l10 10M15 5L5 15" class="hidden group-open:block"/></svg>
                                Menu
                            </span>
                        </summary>
                        <nav aria-label="Menu dasbor" class="halaman absolute inset-x-0 top-full border-b-2 border-pensil/20 bg-kertas pt-2 pb-5 shadow-[0_18px_30px_-20px_rgb(0_0_0/0.45)]">
                            <ul class="grid gap-1 sm:grid-cols-2">
                                @foreach ($menu as [$route, $label, $pattern])
                                    @php($aktif = request()->routeIs($pattern))
                                    <li>
                                        <a href="{{ route($route) }}" @if ($aktif) aria-current="page" @endif class="flex min-h-12 items-center rounded-md px-2 font-display text-[1.1rem] font-bold hover:bg-langit/60">
                                            <span @class(['stabilo' => $aktif])>{{ $label }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t-2 border-dashed border-pensil/20 pt-4">
                                <p class="text-[0.9375rem]"><span class="font-bold">{{ $akun->nama }}</span> · {{ $akun->id_akun }}</p>
                                <x-auth.sign-out />
                            </div>
                        </nav>
                    </details>
                </div>
            </div>
        </header>

        <main id="konten" class="min-w-0 flex-1 px-4 pt-7 pb-20 pl-8 sm:px-8 lg:px-12 lg:pt-10">
            <div class="mx-auto max-w-[72rem]">
                <x-dasbor.flash />
                {{ $slot }}
            </div>
        </main>
    </div>
</x-layouts.app>
