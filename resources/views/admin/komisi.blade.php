@use('App\Support\Format')

<x-dasbor.layout title="Komisi">
    <x-dasbor.judul title="Komisi platform" note="berlaku untuk pesanan yang dibayar setelah diubah" />

    <div class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)] lg:items-start">
        <x-dasbor.panel heading="Persentase sekarang">
            <p class="font-display text-[3rem] leading-none font-extrabold"><span class="stabilo">{{ $persenSekarang }}%</span></p>
            <p class="mt-2 text-[0.9375rem] text-pensil-muda">{{ $diubahPada ? 'Terakhir diubah '.Format::waktu($diubahPada) : 'Nilai bawaan, belum pernah diubah.' }}</p>

            <form method="post" action="{{ route('admin.komisi.update') }}" class="mt-6 space-y-4" novalidate>
                @csrf
                @method('put')
                <x-dasbor.field name="persentase" label="Persentase baru (%)" hint="0 sampai 50. Komisi pesanan yang sudah dibayar tidak ikut berubah.">
                    <input id="isian-persentase" name="persentase" type="number" step="0.01" min="0" max="50" inputmode="decimal" value="{{ old('persentase', $persenSekarang) }}" class="kontrol w-40"
                           aria-describedby="isian-persentase-petunjuk @error('persentase') isian-persentase-galat @enderror" @error('persentase') aria-invalid="true" @enderror>
                </x-dasbor.field>
                <button type="submit" class="tombol">Simpan persentase</button>
            </form>
        </x-dasbor.panel>

        <x-dasbor.panel heading="Komisi terakhir">
            @if ($komisiTerakhir->isEmpty())
                <x-dasbor.kosong message="Belum ada komisi. Komisi dicatat saat pesanan dibayar." />
            @else
                <div class="overflow-x-auto">
                    <table class="tabel">
                        <thead><tr><th scope="col">Pesanan</th><th scope="col">Dibayar</th><th scope="col" class="text-right">Persen</th><th scope="col" class="text-right">Nominal</th></tr></thead>
                        <tbody>
                            @foreach ($komisiTerakhir as $komisi)
                                <tr>
                                    <td><a href="{{ route('admin.pesanan.show', $komisi->id_pesanan) }}" class="tautan angka">{{ $komisi->id_pesanan }}</a><span class="block text-[0.8125rem] text-pensil-muda">{{ $komisi->pesanan->tutor->nama }}</span></td>
                                    <td class="whitespace-nowrap">{{ Format::tanggalPendek($komisi->created_at) }}</td>
                                    <td class="angka text-right">{{ rtrim(rtrim($komisi->persentase, '0'), '.') }}%</td>
                                    <td class="angka text-right">{{ Format::rupiah($komisi->nominal) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-dasbor.panel>
    </div>
</x-dasbor.layout>
