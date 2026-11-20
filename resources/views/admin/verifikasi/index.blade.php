@use('App\Support\Format')

<x-dasbor.layout title="Verifikasi tutor">
    <x-dasbor.judul title="Verifikasi tutor" note="tutor eksternal yang berkasnya menunggu diperiksa, yang paling lama di atas" />

    <x-dasbor.panel>
        @if ($antrean->isEmpty())
            <x-dasbor.kosong message="Antrean kosong. Semua berkas tutor sudah diperiksa." />
        @else
            <div class="overflow-x-auto">
                <table class="tabel">
                    <thead><tr><th scope="col">Tutor</th><th scope="col">Mapel</th><th scope="col">Kecamatan</th><th scope="col">Berkas</th><th scope="col">Diajukan</th><th scope="col"><span class="sr-only">Aksi</span></th></tr></thead>
                    <tbody>
                        @foreach ($antrean as $tutor)
                            <tr>
                                <td><span class="font-bold">{{ $tutor->nama }}</span><span class="block text-[0.8125rem] text-pensil-muda">{{ $tutor->id_tutor }}</span></td>
                                <td>{{ $tutor->mataPelajaran->map->label()->join(', ') }}</td>
                                <td>{{ $tutor->profil?->lokasi ?? '–' }}</td>
                                <td>{{ $tutor->dokumen->where('status_verifikasi', \App\Enums\StatusVerifikasi::Menunggu)->count() }} menunggu</td>
                                <td class="whitespace-nowrap">{{ Format::tanggalPendek($tutor->diajukan_pada ? \Illuminate\Support\Carbon::parse($tutor->diajukan_pada) : null) }}</td>
                                <td><a href="{{ route('admin.verifikasi.show', $tutor) }}" class="tombol-garis min-h-10">Periksa<span class="sr-only"> {{ $tutor->nama }}</span></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-dasbor.panel>
</x-dasbor.layout>
