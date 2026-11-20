<x-auth.page title="Beranda">
    <x-slot:nav>
        <x-auth.sign-out />
    </x-slot:nav>

    <x-slot:aside>
        <h1 class="font-display text-[clamp(2.4rem,6vw,4.6rem)] leading-[0.98] font-extrabold tracking-[-0.025em] [font-stretch:88%]">
            Selamat datang, <span class="stabilo stabilo-sapu">{{ $akun->nama }}</span>
        </h1>
        <p class="mt-5 max-w-[30rem] text-[1.0625rem] leading-[1.75rem]">
            Halaman ini masih disiapkan. Nanti di sini Anda bisa melihat pesanan, jadwal sesi, dan tagihan.
        </p>
        <a href="{{ route('tutor.index') }}" class="mt-8 inline-flex min-h-13 items-center rounded-[0.7rem_1.1rem_0.6rem_1.2rem] bg-tinta px-7 font-display text-[1.2rem] font-bold text-white shadow-[4px_4px_0_var(--color-stabilo)]">
            Cari tutor
        </a>
    </x-slot:aside>

    <x-auth.sheet heading="Anak yang terdaftar" note="akun {{ $akun->id_akun }}">
        @if ($daftarSiswa->isEmpty())
            <p class="leading-[1.75rem]">Belum ada data anak di akun ini.</p>
        @else
            <ul>
                @foreach ($daftarSiswa as $siswa)
                    <li class="flex items-baseline justify-between gap-4 border-b border-dashed border-pensil-muda py-3 last:border-0">
                        <span class="font-tangan text-[1.35rem] leading-tight text-tinta">{{ $siswa->nama }}</span>
                        <span class="shrink-0 font-bold">{{ $siswa->jenjang }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-auth.sheet>
</x-auth.page>
