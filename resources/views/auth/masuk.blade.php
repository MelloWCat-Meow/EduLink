<x-auth.page title="Masuk">
    <x-slot:aside>
        <h1 class="max-w-[12ch] font-display text-[clamp(2.4rem,6vw,4.6rem)] leading-[0.98] font-extrabold tracking-[-0.025em] [font-stretch:88%]">
            Masuk, lalu lanjutkan <span class="stabilo stabilo-sapu">mencari guru</span>.
        </h1>
        <p class="mt-5 max-w-[30rem] text-[1.0625rem] leading-[1.75rem]">
            Pakai email dan kata sandi yang Anda buat saat mendaftar. Orang tua, tutor, dan admin masuk di sini.
        </p>
        <p class="mt-8 flex max-w-[24rem] rotate-[-2deg] items-start gap-2 font-tangan text-[1.3rem] leading-snug text-tinta">
            <x-landing.tick class="mt-0.5 size-7 shrink-0" />
            Belum punya akun? Daftarnya cuma butuh nama, email, dan kata sandi.
        </p>
        <p class="mt-5 flex max-w-[26rem] items-start gap-2 font-tangan text-[1.25rem] leading-snug text-tinta">
            <x-landing.tick class="mt-0.5 size-7 shrink-0" />
            <span>Sudah mengajar di bimbel EduLink? <a href="{{ route('tutor.aktivasi') }}" class="underline decoration-2 underline-offset-4">Aktifkan akun</a></span>
        </p>
    </x-slot:aside>

    <x-auth.sheet heading="Lembar masuk" note="diisi oleh: pemilik akun">
        <x-auth.error-summary />

        <form method="POST" action="{{ route('login.store') }}" novalidate class="space-y-7">
            @csrf

            <x-auth.field name="kontak" label="Email" type="email" autocomplete="username" inputmode="email" required autofocus />
            <x-auth.field name="password" label="Kata sandi" type="password" autocomplete="current-password" required />

            <label for="isian-ingat" class="flex min-h-11 w-fit items-center gap-3 font-bold">
                <input id="isian-ingat" type="checkbox" name="ingat" value="1" class="centang" @checked(old('ingat'))>
                Ingat saya di perangkat ini
            </label>

            <div class="flex flex-wrap items-center gap-x-6 gap-y-4 pt-2">
                <button type="submit" class="inline-flex min-h-13 items-center rounded-[0.7rem_1.1rem_0.6rem_1.2rem] bg-tinta px-8 font-display text-[1.25rem] font-bold text-white shadow-[4px_4px_0_var(--color-stabilo)] transition-[translate,box-shadow] hover:-translate-y-0.5 hover:shadow-[6px_6px_0_var(--color-stabilo)] active:translate-y-0 active:shadow-[2px_2px_0_var(--color-stabilo)] motion-reduce:transition-none">
                    Masuk
                </button>
                <a href="{{ route('register') }}" class="inline-flex min-h-11 items-center font-bold underline decoration-2 underline-offset-4">
                    Belum punya akun? Daftar
                </a>
            </div>
        </form>
    </x-auth.sheet>
</x-auth.page>
