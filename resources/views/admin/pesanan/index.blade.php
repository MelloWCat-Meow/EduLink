@use('App\Support\Format')

<x-dasbor.layout title="Monitoring pesanan">
    <x-dasbor.judul title="Monitoring pesanan" note="hanya baca, kecuali membatalkan pesanan bermasalah" />

    <form method="get" action="{{ route('admin.pesanan.index') }}" class="mb-5 flex flex-wrap items-end gap-3 bg-white px-4 py-4 shadow-[0_14px_30px_-24px_rgb(37_37_43/0.5)]">
        <label class="block w-full text-[0.875rem] font-bold sm:w-auto">Status
            <select name="status" class="kontrol mt-0.5 w-full sm:w-56">
                <option value="">Semua status</option>
                @foreach (\App\Enums\StatusPesanan::cases() as $status)
                    <option value="{{ $status->value }}" @selected($statusDipilih === $status)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </label>
        <label class="block w-full text-[0.875rem] font-bold sm:w-auto">Tutor
            <select name="tutor" class="kontrol mt-0.5 w-full sm:w-52">
                <option value="">Semua tutor</option>
                @foreach ($pilihanTutor as $tutor)
                    <option value="{{ $tutor->id_tutor }}" @selected($tutorDipilih === $tutor->id_tutor)>{{ $tutor->nama }}</option>
                @endforeach
            </select>
        </label>
        <label class="block w-full text-[0.875rem] font-bold sm:w-auto">Periode dibuat
            <input type="month" name="periode" value="{{ $periode }}" class="kontrol mt-0.5 w-full sm:w-44">
        </label>
        <button type="submit" class="tombol">Saring</button>
        <a href="{{ route('admin.pesanan.index') }}" class="tautan self-center">Hapus saringan</a>
    </form>

    <x-dasbor.panel>
        @if ($daftarPesanan->isEmpty())
            <x-dasbor.kosong message="Tidak ada pesanan yang cocok dengan saringan ini." />
        @else
            <p class="mb-3 text-[0.9375rem] text-pensil-muda">{{ $daftarPesanan->total() }} pesanan</p>
            <div class="overflow-x-auto">
                <table class="tabel">
                    <thead><tr><th scope="col">Nomor</th><th scope="col">Dibuat</th><th scope="col">Orang tua / anak</th><th scope="col">Tutor</th><th scope="col">Mapel</th><th scope="col">Status</th><th scope="col" class="text-right">Total</th></tr></thead>
                    <tbody>
                        @foreach ($daftarPesanan as $pesanan)
                            <tr>
                                <td><a href="{{ route('admin.pesanan.show', $pesanan) }}" class="tautan angka">{{ $pesanan->id_pesanan }}</a></td>
                                <td class="whitespace-nowrap">{{ Format::tanggalPendek($pesanan->created_at) }}</td>
                                <td>{{ $pesanan->siswa->wali->nama }}<span class="block text-[0.8125rem] text-pensil-muda">{{ $pesanan->siswa->nama }}</span></td>
                                <td>{{ $pesanan->tutor->nama }}</td>
                                <td class="whitespace-nowrap">{{ $pesanan->mapel->label() }}</td>
                                <td><x-dasbor.status :status="$pesanan->status" /></td>
                                <td class="angka text-right">{{ Format::rupiah($pesanan->total_biaya) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $daftarPesanan->links('components.dasbor.halaman') }}
        @endif
    </x-dasbor.panel>
</x-dasbor.layout>
