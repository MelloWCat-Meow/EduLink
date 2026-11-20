<x-auth.page title="Daftar akun orang tua">
    <x-slot:aside>
        <h1 class="max-w-[12ch] font-display text-[clamp(2.4rem,6vw,4.6rem)] leading-[0.98] font-extrabold tracking-[-0.025em] [font-stretch:88%]">
            Buat akun <span class="stabilo stabilo-sapu">orang tua</span>.
        </h1>
        <p class="mt-5 max-w-[30rem] text-[1.0625rem] leading-[1.75rem]">
            Dengan akun ini Anda bisa memesan sesi les, membayar di EduLink, dan menulis ulasan setelah sesi selesai.
        </p>

        <ul class="mt-8 max-w-[26rem] space-y-4 font-tangan text-[1.25rem] leading-snug text-tinta">
            <li class="flex items-start gap-2">
                <x-landing.tick class="mt-0.5 size-7 shrink-0" />
                Selesai daftar, Anda langsung masuk.
            </li>
            <li class="flex items-start gap-2">
                <x-landing.tick class="mt-0.5 size-7 shrink-0" />
                <span>Mau mengajar? <a href="{{ route('tutor.register') }}" class="underline decoration-2 underline-offset-4">Pendaftaran tutor</a> punya lembar sendiri.</span>
            </li>
        </ul>
    </x-slot:aside>

    <x-auth.sheet heading="Lembar pendaftaran" note="diisi oleh: orang tua atau wali">
        <x-auth.error-summary />

        <form method="POST" action="{{ route('register.store') }}" novalidate class="space-y-7">
            @csrf

            <x-auth.field name="nama" label="Nama lengkap" autocomplete="name" required autofocus maxlength="100" />
            <x-auth.field name="kontak" label="Email" type="email" autocomplete="email" inputmode="email" required maxlength="50" hint="Dipakai untuk masuk dan menerima kabar pesanan." />
            <x-auth.field name="no_hp" label="Nomor HP (WhatsApp)" type="tel" autocomplete="tel" inputmode="tel" required maxlength="20" hint="Tutor bisa menghubungi Anda lewat WhatsApp setelah pesanan dibayar." />
            <x-auth.field name="password" label="Kata sandi" type="password" autocomplete="new-password" required minlength="8" hint="Minimal 8 karakter." />
            <x-auth.field name="password_confirmation" label="Ulangi kata sandi" type="password" autocomplete="new-password" required minlength="8" />

            <div class="flex flex-wrap items-center gap-x-6 gap-y-4 pt-2">
                <button type="submit" class="inline-flex min-h-13 items-center rounded-[0.7rem_1.1rem_0.6rem_1.2rem] bg-tinta px-8 font-display text-[1.25rem] font-bold text-white shadow-[4px_4px_0_var(--color-stabilo)] transition-[translate,box-shadow] hover:-translate-y-0.5 hover:shadow-[6px_6px_0_var(--color-stabilo)] active:translate-y-0 active:shadow-[2px_2px_0_var(--color-stabilo)] motion-reduce:transition-none">
                    Buat akun
                </button>
                <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center font-bold underline decoration-2 underline-offset-4">
                    Sudah punya akun? Masuk
                </a>
            </div>
        </form>
    </x-auth.sheet>
</x-auth.page>
