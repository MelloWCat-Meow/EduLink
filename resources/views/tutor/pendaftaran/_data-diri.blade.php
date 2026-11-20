@use('App\Enums\LangkahPendaftaran')

<x-tutor.bagian nomor="1" judul="Data diri" :perbaiki="$tutor?->perluDiperbaiki(LangkahPendaftaran::DataDiri) ?? false" :catatan="$tutor?->catatanPerbaikan(LangkahPendaftaran::DataDiri)">
    <x-auth.error-summary />

    @if ($tutor === null)
        <form method="POST" action="{{ route('tutor.register.store') }}" novalidate class="space-y-7">
            @csrf
            <x-auth.field name="nama" label="Nama lengkap" autocomplete="name" required autofocus maxlength="100" hint="Sesuai KTP." />
            <x-auth.field name="kontak" label="Email" type="email" autocomplete="email" inputmode="email" required maxlength="50" hint="Dipakai untuk masuk dan menerima hasil pemeriksaan." />
            <x-auth.field name="no_hp" label="Nomor HP (WhatsApp)" type="tel" autocomplete="tel" inputmode="tel" required maxlength="20" hint="Misalnya 0812 3456 7890." />
            <x-auth.field name="password" label="Kata sandi" type="password" autocomplete="new-password" required minlength="8" hint="Minimal 8 karakter." />
            <x-auth.field name="password_confirmation" label="Ulangi kata sandi" type="password" autocomplete="new-password" required minlength="8" />

            <div class="flex flex-wrap items-center gap-x-6 gap-y-4 pt-2">
                @include('tutor.pendaftaran._tombol', ['teksTombol' => 'Buat akun dan lanjut'])
                <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center font-bold underline decoration-2 underline-offset-4">Sudah punya akun? Masuk</a>
            </div>
        </form>
    @else
        <form method="POST" action="{{ route('tutor.pendaftaran.data-diri') }}" novalidate class="space-y-7">
            @csrf
            @method('PUT')
            <x-auth.field name="nama" label="Nama lengkap" autocomplete="name" required maxlength="100" :value="$tutor->nama" hint="Sesuai KTP." />
            <x-auth.field name="no_hp" label="Nomor HP (WhatsApp)" type="tel" autocomplete="tel" inputmode="tel" required maxlength="20" :value="$tutor->no_hp" />

            <div>
                <p class="font-bold">Email</p>
                <p class="mt-1 font-tangan text-[1.3rem] text-tinta">{{ $tutor->kontak }}</p>
                <p class="text-[0.9375rem] text-pensil-muda">Email dipakai untuk masuk, jadi tidak bisa diubah di sini.</p>
            </div>

            <div class="pt-2">@include('tutor.pendaftaran._tombol')</div>
        </form>
    @endif
</x-tutor.bagian>
