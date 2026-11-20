@use('App\Support\Format')

<x-dasbor.layout title="Data pengguna">
    <x-dasbor.judul title="Data pengguna" note="akun orang tua, hanya baca" />

    <x-dasbor.panel>
        @if ($daftarAkun->isEmpty())
            <x-dasbor.kosong message="Belum ada akun orang tua." />
        @else
            <div class="overflow-x-auto">
                <table class="tabel">
                    <thead><tr><th scope="col">ID akun</th><th scope="col">Nama</th><th scope="col">Email</th><th scope="col" class="text-right">Anak</th><th scope="col" class="text-right">Pesanan</th><th scope="col">Terdaftar</th></tr></thead>
                    <tbody>
                        @foreach ($daftarAkun as $akun)
                            <tr>
                                <td class="angka">{{ $akun->id_akun }}</td>
                                <td class="font-bold">{{ $akun->nama }}</td>
                                <td>{{ $akun->kontak }}</td>
                                <td class="angka text-right">{{ $akun->siswa_count }}</td>
                                <td class="angka text-right">{{ $akun->pesanan_count }}</td>
                                <td class="whitespace-nowrap">{{ Format::tanggalPendek($akun->created_at) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $daftarAkun->links('components.dasbor.halaman') }}
        @endif
    </x-dasbor.panel>
</x-dasbor.layout>
