@props(['kode', 'judul', 'pesan', 'catatan'])

{{--
    Friendly error page for 404, 419 and 429, in the sign-in pages' style.
    The default button goes to the role's home when signed in, else the landing.
--}}
<x-auth.page :title="$judul">
    <x-slot:aside>
        <h1 class="max-w-[14ch] font-display text-[clamp(2.4rem,6vw,4.6rem)] leading-[0.98] font-extrabold tracking-[-0.025em] [font-stretch:88%]">
            {{ $judul }}
        </h1>
        <p class="mt-5 max-w-[30rem] text-[1.0625rem] leading-[1.75rem]">{{ $pesan }}</p>
    </x-slot:aside>

    <x-auth.sheet :heading="'Kode '.$kode" :note="$catatan">
        <div class="flex flex-wrap items-center gap-x-6 gap-y-4">
            @if ($slot->isNotEmpty())
                {{ $slot }}
            @else
                @auth
                    <a href="{{ route(auth()->user()->peran->homeRoute()) }}" class="inline-flex min-h-12 items-center rounded-[0.7rem_1.1rem_0.6rem_1.2rem] bg-tinta px-6 font-bold text-white">Ke beranda saya</a>
                @else
                    <a href="{{ route('landing') }}" class="inline-flex min-h-12 items-center rounded-[0.7rem_1.1rem_0.6rem_1.2rem] bg-tinta px-6 font-bold text-white">Ke halaman utama</a>
                @endauth
                <a href="{{ route('tutor.index') }}" class="inline-flex min-h-11 items-center font-bold underline decoration-2 underline-offset-4">Cari tutor</a>
            @endif
        </div>
    </x-auth.sheet>
</x-auth.page>
