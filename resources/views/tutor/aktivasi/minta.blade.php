<x-auth.page title="Aktifkan akun tutor">
    <x-slot:aside>
        <h1 class="max-w-[13ch] font-display text-[clamp(2.4rem,6vw,4.4rem)] leading-[0.98] font-extrabold tracking-[-0.025em] [font-stretch:88%]">
            Sudah mengajar di <span class="stabilo stabilo-sapu">bimbel EduLink</span>?
        </h1>
        <p class="mt-5 max-w-[30rem] text-[1.0625rem] leading-[1.75rem]">
            Data Anda sudah ada di bimbel, jadi tidak perlu mendaftar ulang. Tulis email yang terdaftar di bimbel, lalu buka tautan yang kami kirim untuk membuat kata sandi.
        </p>
        <p class="mt-8 flex max-w-[26rem] items-start gap-2 font-tangan text-[1.25rem] leading-snug text-tinta">
            <x-landing.tick class="mt-0.5 size-7 shrink-0" />
            <span>Belum pernah mengajar di bimbel? <a href="{{ route('tutor.register') }}" class="underline decoration-2 underline-offset-4">Daftar sebagai tutor baru</a>.</span>
        </p>
    </x-slot:aside>

    <x-auth.sheet heading="Lembar aktivasi" note="diisi oleh: pengajar bimbel">
        @if (session('status'))
            <p role="status" class="mb-6 flex items-start gap-2 border-l-4 border-tinta bg-langit/70 px-4 py-3">
                <svg aria-hidden="true" class="mt-0.5 size-5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5.5h14v9H3zM3.5 6l6.5 5 6.5-5"/></svg>
                {{ session('status') }}
            </p>
        @endif

        @if (session('tautan_demo'))
            <div class="mb-6 border-2 border-dashed border-pensil/40 bg-sticky px-4 py-3">
                <p class="text-[0.8125rem] font-bold tracking-wide uppercase">Mode demo (hanya di lokal)</p>
                <p class="mt-1 text-[0.9375rem]">Email terkirim ke log, jadi tautannya ditampilkan di sini:</p>
                <a href="{{ session('tautan_demo') }}" class="tautan mt-1 block break-all">{{ session('tautan_demo') }}</a>
            </div>
        @endif

        <x-auth.error-summary />

        <form method="POST" action="{{ route('tutor.aktivasi.store') }}" novalidate class="space-y-7">
            @csrf
            <x-auth.field name="kontak" label="Email yang terdaftar di bimbel" type="email" autocomplete="email" inputmode="email" required autofocus maxlength="50" />
            <div class="flex flex-wrap items-center gap-x-6 gap-y-4 pt-2">
                @include('tutor.pendaftaran._tombol', ['teksTombol' => 'Kirim tautan aktivasi'])
                <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center font-bold underline decoration-2 underline-offset-4">Sudah aktif? Masuk</a>
            </div>
        </form>
    </x-auth.sheet>
</x-auth.page>
