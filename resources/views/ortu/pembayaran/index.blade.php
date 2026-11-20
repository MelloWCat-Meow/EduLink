@use('App\Support\Format')

<x-dasbor.layout title="Pembayaran">
    <x-dasbor.judul title="Pembayaran" note="semua percobaan bayar, termasuk yang gagal" />

    <x-dasbor.panel>
        @if ($daftarPembayaran->isEmpty())
            <x-dasbor.kosong message="Belum ada pembayaran. Tagihan muncul setelah tutor menerima pesanan." />
        @else
            <div class="overflow-x-auto">
                <table class="tabel">
                    <thead>
                        <tr><th scope="col">Nomor</th><th scope="col">Pesanan</th><th scope="col">Cara bayar</th><th scope="col" class="text-right">Jumlah</th><th scope="col">Status</th><th scope="col">Waktu</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($daftarPembayaran as $pembayaran)
                            <tr>
                                <td class="angka">{{ $pembayaran->id_pembayaran }}</td>
                                <td>
                                    <a href="{{ route('pesanan.show', $pembayaran->id_pesanan) }}" class="tautan angka">{{ $pembayaran->id_pesanan }}</a>
                                    <span class="block text-[0.875rem] text-pensil-muda">{{ $pembayaran->pesanan->mapel->label() }}, {{ $pembayaran->pesanan->tutor->nama }}</span>
                                </td>
                                <td>{{ $pembayaran->metode->label() }}</td>
                                <td class="angka text-right">{{ Format::rupiah($pembayaran->jumlah) }}</td>
                                <td><x-dasbor.label :tone="$pembayaran->status->tone()">{{ $pembayaran->status->label() }}</x-dasbor.label></td>
                                <td class="whitespace-nowrap">{{ Format::waktu($pembayaran->diproses_pada ?? $pembayaran->created_at) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $daftarPembayaran->links('components.dasbor.halaman') }}
        @endif
    </x-dasbor.panel>
</x-dasbor.layout>
