<x-dasbor.layout title="Pesanan saya">
    <x-dasbor.judul title="Pesanan saya">
        <x-slot:actions><a href="{{ route('tutor.index') }}" class="tombol">Pesan tutor baru</a></x-slot:actions>
    </x-dasbor.judul>

    <x-dasbor.filter-status route="pesanan.index" :dipilih="$statusDipilih" />

    <x-dasbor.panel>
        @if ($daftarPesanan->isEmpty())
            <x-dasbor.kosong :message="$statusDipilih ? 'Tidak ada pesanan berstatus “'.$statusDipilih->label().'”.' : 'Anda belum pernah memesan tutor.'">
                <a href="{{ route('tutor.index') }}" class="tombol">Cari tutor</a>
            </x-dasbor.kosong>
        @else
            <ul>
                @foreach ($daftarPesanan as $pesanan)
                    <x-dasbor.baris-pesanan :pesanan="$pesanan" :href="route('pesanan.show', $pesanan)" />
                @endforeach
            </ul>
            {{ $daftarPesanan->links('components.dasbor.halaman') }}
        @endif
    </x-dasbor.panel>
</x-dasbor.layout>
