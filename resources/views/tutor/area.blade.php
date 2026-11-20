<x-tutor.halaman title="Area tutor">
    <h1 class="font-display text-[clamp(2rem,5vw,3.2rem)] leading-[1.02] font-extrabold tracking-[-0.025em] [font-stretch:88%]">
        Selamat datang, <span class="stabilo">{{ $tutor->nama }}</span>
    </h1>

    <div class="mt-7 grid gap-6 lg:grid-cols-2">
        <x-dasbor.panel heading="Status akun">
            <p class="flex flex-wrap items-center gap-3">
                <x-dasbor.label tone="baik">Aktif · terverifikasi</x-dasbor.label>
                <span class="text-[0.9375rem] text-pensil-muda">{{ $tutor->isInternal() ? 'Pengajar bimbel EduLink' : 'Tutor mitra' }} · {{ $tutor->id_tutor }}</span>
            </p>
            @if ($tampilDiPencarian)
                <p class="mt-4">Profil Anda tampil di pencarian orang tua.</p>
                <a href="{{ route('tutor.show', $tutor) }}" class="tombol mt-4">Lihat profil publik saya</a>
            @else
                <p class="mt-4">Profil Anda belum tampil di pencarian karena tarif atau kecamatan masih kosong. Hubungi admin EduLink untuk melengkapinya.</p>
            @endif
        </x-dasbor.panel>

        <x-dasbor.panel heading="Dashboard tutor">
            <x-dasbor.kosong message="Dashboard tutor segera hadir: menerima pesanan, mengatur jadwal, dan pencairan dana." />
        </x-dasbor.panel>
    </div>
</x-tutor.halaman>
