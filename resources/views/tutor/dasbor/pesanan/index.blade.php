<x-dasbor.layout title="Pesanan">
    <x-dasbor.judul title="Pesanan" note="semua pesanan yang masuk ke Anda, yang terbaru di atas" />

    <x-dasbor.filter-status route="tutor.pesanan.index" :dipilih="$statusDipilih" />

    <x-dasbor.panel>
        @if ($daftarPesanan->isEmpty())
            <x-dasbor.kosong :message="$statusDipilih ? 'Tidak ada pesanan dengan status ini.' : 'Belum ada pesanan. Pastikan jadwal Anda punya slot yang tersedia.'" />
        @else
            <ul>
                @foreach ($daftarPesanan as $pesanan)
                    <x-dasbor.baris-pesanan :pesanan="$pesanan" :href="route('tutor.pesanan.show', $pesanan)" untuk-tutor />
                @endforeach
            </ul>
            {{ $daftarPesanan->links('components.dasbor.halaman') }}
        @endif
    </x-dasbor.panel>
</x-dasbor.layout>
