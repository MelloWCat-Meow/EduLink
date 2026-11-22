@props(['title', 'catatan', 'daftarIsi' => []])

{{--
    A legal document page: the public header, then one readable sheet with a
    numbered table of contents. Short lines and generous spacing for parents
    reading on a phone.
--}}
<x-publik.layout :title="$title">
    <article class="mx-auto max-w-[46rem]">
        <header class="mb-8">
            <h1 class="font-display text-[clamp(2.1rem,5vw,3.2rem)] leading-[1.02] font-extrabold tracking-[-0.025em] [font-stretch:88%]">
                <span class="stabilo">{{ $title }}</span>
            </h1>
            <p class="mt-3 font-tangan text-[1.2rem] text-pensil-muda">{{ $catatan }}</p>
        </header>

        @if ($daftarIsi)
            <nav aria-labelledby="judul-daftar-isi" class="mb-8 rotate-[-0.4deg] bg-sticky px-5 py-4 shadow-[0_12px_24px_-18px_rgb(0_0_0/0.45)]">
                <h2 id="judul-daftar-isi" class="font-tangan text-[1.35rem] font-bold text-tinta">Isi dokumen</h2>
                <ol class="mt-2 list-decimal space-y-1 pl-6">
                    @foreach ($daftarIsi as $id => $judul)
                        <li><a href="#{{ $id }}" class="tautan">{{ $judul }}</a></li>
                    @endforeach
                </ol>
            </nav>
        @endif

        <div class="dokumen space-y-10 bg-white px-5 py-7 text-[1.0625rem] leading-[1.75] shadow-[0_1px_0_rgb(0_0_0/0.04),0_22px_40px_-28px_rgb(37_37_43/0.55)] sm:px-9">
            {{ $slot }}
        </div>

        <p class="mt-6 border-l-4 border-cap bg-[rgb(192_38_45/0.05)] px-4 py-3 text-[0.9375rem]">
            <span class="font-bold">Catatan:</span> dokumen ini adalah rancangan untuk keperluan tugas mata kuliah dan belum ditinjau oleh ahli hukum.
        </p>
    </article>
</x-publik.layout>
