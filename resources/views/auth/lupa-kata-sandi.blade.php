<x-auth.page title="Lupa kata sandi">
    <x-slot:aside>
        <h1 class="max-w-[12ch] font-display text-[clamp(2.4rem,6vw,4.4rem)] leading-[0.98] font-extrabold tracking-[-0.025em] [font-stretch:88%]">
            Lupa <span class="stabilo stabilo-sapu">kata sandi</span>?
        </h1>
        <p class="mt-5 max-w-[30rem] text-[1.0625rem] leading-[1.75rem]">
            Tulis email yang Anda pakai untuk masuk. Kami kirim tautan untuk membuat kata sandi baru.
        </p>
        <p class="mt-8 flex max-w-[26rem] items-start gap-2 font-tangan text-[1.25rem] leading-snug text-tinta">
            <x-landing.tick class="mt-0.5 size-7 shrink-0" />
            <span>Pengajar bimbel EduLink yang belum pernah masuk? Pakai <a href="{{ route('tutor.aktivasi') }}" class="underline decoration-2 underline-offset-4">aktivasi akun tutor</a>.</span>
        </p>
    </x-slot:aside>

    <x-auth.sheet heading="Lembar lupa kata sandi" note="semua peran">
        @if (session('status'))
            <p role="status" class="mb-6 border-l-4 border-tinta bg-langit/70 px-4 py-3">{{ session('status') }}</p>
        @endif

        @if (session('tautan_demo'))
            <div class="mb-6 border-2 border-dashed border-pensil/40 bg-sticky px-4 py-3">
                <p class="text-[0.8125rem] font-bold tracking-wide uppercase">Mode demo</p>
                <p class="mt-1 text-[0.9375rem]">Email terkirim ke log, jadi tautannya ditampilkan di sini:</p>
                <a href="{{ session('tautan_demo') }}" class="tautan mt-1 block break-all">{{ session('tautan_demo') }}</a>
            </div>
        @endif

        <x-auth.error-summary />

        <form method="POST" action="{{ route('password.email') }}" novalidate class="space-y-7">
            @csrf
            <x-auth.field name="kontak" label="Email" type="email" autocomplete="email" inputmode="email" required autofocus maxlength="50" />
            <div class="flex flex-wrap items-center gap-x-6 gap-y-4 pt-2">
                @include('tutor.pendaftaran._tombol', ['teksTombol' => 'Kirim tautan'])
                <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center font-bold underline decoration-2 underline-offset-4">Kembali ke halaman masuk</a>
            </div>
        </form>
    </x-auth.sheet>
</x-auth.page>
