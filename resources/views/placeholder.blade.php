@php
    $filters = ($showsSearchFilters ?? false)
        ? array_filter(request()->only(['mapel', 'jenjang', 'lokasi']), 'filled')
        : [];
@endphp

<x-layouts.app :title="$title">
    <main id="konten" class="mx-auto flex min-h-screen max-w-xl flex-col justify-center px-4 py-16 sm:px-6">
        <a href="{{ route('landing') }}" class="text-lg font-extrabold text-dongker">EduLink <span class="font-semibold">Tutor</span></a>

        <h1 class="mt-8 text-3xl font-extrabold tracking-tight">{{ $title }}</h1>
        <p class="mt-3 leading-relaxed text-abu">Halaman ini sedang disiapkan dan belum bisa digunakan.</p>

        @if ($filters)
            <div class="mt-6 rounded-lg border border-garis bg-kertas p-4">
                <h2 class="text-sm font-bold">Pencarian Anda</h2>
                <dl class="mt-2 grid grid-cols-[6rem_1fr] gap-y-1 text-sm">
                    @foreach ($filters as $key => $value)
                        <dt class="text-abu">{{ ucfirst($key) }}</dt>
                        <dd class="font-medium">{{ $key === 'jenjang' ? strtoupper($value) : $value }}</dd>
                    @endforeach
                </dl>
            </div>
        @endif

        <a href="{{ route('landing') }}" class="mt-8 inline-flex min-h-12 w-fit items-center rounded-md bg-dongker px-5 font-semibold text-white hover:bg-dongker-muda">Kembali ke beranda</a>
    </main>
</x-layouts.app>
