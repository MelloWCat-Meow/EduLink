@use('App\Support\Format')

<x-dasbor.layout title="Ulasan">
    <x-dasbor.judul title="Ulasan" note="ulasan yang tampil di profil publik Anda" />

    <x-dasbor.panel>
        <div class="mb-4"><x-tutor.rating :rata="$rating->rata_rating" :jumlah="$rating->jumlah_ulasan" class="text-[1.1rem]" /></div>
        @if ($daftarUlasan->isEmpty())
            <x-dasbor.kosong message="Belum ada ulasan yang tampil." />
        @else
            <ul class="divide-y divide-pensil/12">
                @foreach ($daftarUlasan as $ulasan)
                    <li class="py-4">
                        <p class="flex flex-wrap items-baseline gap-x-3"><span class="font-display text-[1.2rem] font-extrabold">{{ $ulasan->nilai }} dari 5</span><span class="text-[0.875rem] text-pensil-muda">{{ $ulasan->pesanan->mapel->label() }} · wali {{ $ulasan->pesanan->siswa->nama }} · {{ Format::tanggalPendek($ulasan->created_at) }}</span></p>
                        @if ($ulasan->komentar)
                            <p class="mt-1 font-tangan text-[1.25rem] leading-snug text-tinta">“{{ $ulasan->komentar }}”</p>
                        @endif
                    </li>
                @endforeach
            </ul>
            {{ $daftarUlasan->links('components.dasbor.halaman') }}
        @endif
    </x-dasbor.panel>
</x-dasbor.layout>
