@php
    $filters = ($showsSearchFilters ?? false)
        ? array_filter(request()->only(['mapel', 'jenjang', 'lokasi']), 'filled')
        : [];
@endphp

<x-layouts.app :title="$title">
    <main id="konten" class="halaman flex min-h-screen flex-col justify-center py-16">
        <div class="mx-auto w-full max-w-xl">
            <a href="{{ route('landing') }}" class="font-display text-[1.45rem] leading-none font-extrabold [font-stretch:85%]">
                EduLink <span class="font-tangan text-[1.35rem] font-bold text-tinta">tutor</span>
            </a>

            <h1 class="mt-8 font-display text-[2.4rem] leading-tight font-extrabold tracking-tight [font-stretch:88%]">{{ $title }}</h1>
            <p class="mt-3 text-[1.0625rem] leading-[1.75rem]">{{ $message ?? 'Halaman ini sedang disiapkan dan belum bisa digunakan.' }}</p>

            @if ($filters)
                <div class="mt-6 rotate-[-0.6deg] bg-white p-5 shadow-[0_16px_28px_-22px_rgb(0_0_0/0.5)]">
                    <h2 class="font-display text-[1.2rem] font-extrabold">Pencarian Anda</h2>
                    <dl class="mt-2 grid grid-cols-[6rem_1fr] gap-y-1">
                        @foreach ($filters as $key => $value)
                            <dt class="text-pensil-muda">{{ ucfirst($key) }}</dt>
                            <dd class="font-tangan text-[1.2rem] text-tinta">{{ $key === 'jenjang' ? strtoupper($value) : $value }}</dd>
                        @endforeach
                    </dl>
                </div>
            @endif

            <a href="{{ route('landing') }}" class="mt-8 inline-flex min-h-12 w-fit items-center rounded-[0.7rem_1.1rem_0.6rem_1.2rem] bg-tinta px-6 font-bold text-white">Kembali ke beranda</a>
        </div>
    </main>
</x-layouts.app>
