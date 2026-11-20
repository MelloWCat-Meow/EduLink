<x-auth.page title="Bukan halaman untuk akun ini">
    <x-slot:aside>
        <h1 class="max-w-[14ch] font-display text-[clamp(2.4rem,6vw,4.6rem)] leading-[0.98] font-extrabold tracking-[-0.025em] [font-stretch:88%]">
            Halaman ini bukan untuk akun Anda.
        </h1>
        <p class="mt-5 max-w-[30rem] text-[1.0625rem] leading-[1.75rem]">
            Akun yang sedang masuk tidak punya akses ke halaman tersebut. Kembali ke beranda akun Anda untuk melanjutkan.
        </p>
    </x-slot:aside>

    <x-auth.sheet heading="Kode 403" note="akses ditolak">
        @auth
            <a href="{{ route(auth()->user()->peran->homeRoute()) }}" class="inline-flex min-h-12 items-center rounded-[0.7rem_1.1rem_0.6rem_1.2rem] bg-tinta px-6 font-bold text-white">
                Ke beranda saya
            </a>
        @else
            <a href="{{ route('landing') }}" class="inline-flex min-h-12 items-center rounded-[0.7rem_1.1rem_0.6rem_1.2rem] bg-tinta px-6 font-bold text-white">
                Ke halaman utama
            </a>
        @endauth
    </x-auth.sheet>
</x-auth.page>
