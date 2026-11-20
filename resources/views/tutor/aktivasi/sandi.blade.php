<x-auth.page title="Buat kata sandi tutor">
    <x-slot:aside>
        <h1 class="max-w-[12ch] font-display text-[clamp(2.4rem,6vw,4.4rem)] leading-[0.98] font-extrabold tracking-[-0.025em] [font-stretch:88%]">
            Halo, <span class="stabilo stabilo-sapu">{{ $tutor->nama }}</span>.
        </h1>
        <p class="mt-5 max-w-[30rem] text-[1.0625rem] leading-[1.75rem]">
            Buat kata sandi untuk akun tutor Anda. Setelah itu Anda langsung masuk dan tinggal melengkapi tarif, kecamatan, dan jadwal.
        </p>
    </x-slot:aside>

    <x-auth.sheet heading="Buat kata sandi" note="tautan hanya bisa dipakai sekali">
        <x-dasbor.flash />
        <x-auth.error-summary />

        <form method="POST" action="{{ $aksi }}" novalidate class="space-y-7">
            @csrf
            <div>
                <p class="font-bold">Email untuk masuk</p>
                <p class="mt-1 font-tangan text-[1.3rem] text-tinta">{{ $tutor->kontak }}</p>
            </div>
            <x-auth.field name="password" label="Kata sandi" type="password" autocomplete="new-password" required minlength="8" hint="Minimal 8 karakter." autofocus />
            <x-auth.field name="password_confirmation" label="Ulangi kata sandi" type="password" autocomplete="new-password" required minlength="8" />
            <div class="pt-2">@include('tutor.pendaftaran._tombol', ['teksTombol' => 'Aktifkan akun'])</div>
        </form>
    </x-auth.sheet>
</x-auth.page>
