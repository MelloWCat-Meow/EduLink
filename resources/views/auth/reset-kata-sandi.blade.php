<x-auth.page title="Buat kata sandi baru">
    <x-slot:aside>
        <h1 class="max-w-[12ch] font-display text-[clamp(2.4rem,6vw,4.4rem)] leading-[0.98] font-extrabold tracking-[-0.025em] [font-stretch:88%]">
            Buat kata sandi <span class="stabilo stabilo-sapu">baru</span>.
        </h1>
        <p class="mt-5 max-w-[30rem] text-[1.0625rem] leading-[1.75rem]">
            Setelah disimpan, semua perangkat yang masih masuk dengan akun ini akan dikeluarkan.
        </p>
    </x-slot:aside>

    <x-auth.sheet heading="Kata sandi baru" note="tautan hanya bisa dipakai sekali">
        <x-auth.error-summary />

        <form method="POST" action="{{ route('password.update') }}" novalidate class="space-y-7">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-auth.field name="kontak" label="Email" type="email" autocomplete="username" required :value="$kontak" />
            <x-auth.field name="password" label="Kata sandi baru" type="password" autocomplete="new-password" required minlength="8" hint="Minimal 8 karakter." autofocus />
            <x-auth.field name="password_confirmation" label="Ulangi kata sandi baru" type="password" autocomplete="new-password" required minlength="8" />
            <div class="pt-2">@include('tutor.pendaftaran._tombol', ['teksTombol' => 'Simpan kata sandi'])</div>
        </form>
    </x-auth.sheet>
</x-auth.page>
