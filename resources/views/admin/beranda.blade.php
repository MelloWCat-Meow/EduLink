<x-auth.page title="Admin">
    <x-slot:nav>
        <x-auth.sign-out />
    </x-slot:nav>

    <x-slot:aside>
        <h1 class="font-display text-[clamp(2.4rem,6vw,4.6rem)] leading-[0.98] font-extrabold tracking-[-0.025em] [font-stretch:88%]">
            Selamat datang, Admin <span class="stabilo stabilo-sapu">{{ auth()->user()->nama }}</span>
        </h1>
        <p class="mt-5 max-w-[30rem] text-[1.0625rem] leading-[1.75rem]">
            Halaman admin masih disiapkan. Pemeriksaan berkas tutor dan pengelolaan akun akan ada di sini.
        </p>
    </x-slot:aside>

    <x-auth.sheet heading="Catatan admin" note="akun {{ auth()->user()->id_akun }}">
        <p class="leading-[1.75rem]">Belum ada fitur yang bisa dipakai. Akun admin hanya dibuat dari seeder, tidak bisa lewat halaman daftar.</p>
    </x-auth.sheet>
</x-auth.page>
