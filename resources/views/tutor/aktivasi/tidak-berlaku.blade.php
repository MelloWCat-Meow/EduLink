<x-auth.page :title="$judul">
    <x-slot:aside>
        <h1 class="max-w-[12ch] font-display text-[clamp(2.4rem,6vw,4.4rem)] leading-[0.98] font-extrabold tracking-[-0.025em] [font-stretch:88%]">
            <span class="stabilo">{{ $judul }}</span>
        </h1>
    </x-slot:aside>

    <x-auth.sheet heading="Aktivasi akun tutor">
        <p class="text-[1.0625rem] leading-[1.75rem]">{{ $pesan }}</p>
        <div class="mt-6 flex flex-wrap gap-x-6 gap-y-3">
            <a href="{{ route('tutor.aktivasi') }}" class="tombol">Minta tautan baru</a>
            <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center font-bold underline decoration-2 underline-offset-4">Masuk</a>
        </div>
    </x-auth.sheet>
</x-auth.page>
