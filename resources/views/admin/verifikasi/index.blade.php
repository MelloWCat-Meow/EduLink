@use('App\Support\Format')

<x-dasbor.layout title="Verifikasi tutor">
    <x-dasbor.judul title="Verifikasi tutor" note="pendaftaran yang menunggu diperiksa, yang paling lama dikirim di atas" />

    <x-dasbor.panel>
        @if ($antrean->isEmpty())
            <x-dasbor.kosong message="Antrean kosong. Semua pendaftaran tutor sudah diperiksa." />
        @else
            <div class="overflow-x-auto">
                <table class="tabel">
                    <thead><tr><th scope="col">Tutor</th><th scope="col">Jenis</th><th scope="col">Mapel</th><th scope="col">Kecamatan</th><th scope="col">Dokumen</th><th scope="col">Dikirim</th><th scope="col"><span class="sr-only">Aksi</span></th></tr></thead>
                    <tbody>
                        @foreach ($antrean as $tutor)
                            <tr>
                                <td><span class="font-bold">{{ $tutor->nama }}</span><span class="block text-[0.8125rem] text-pensil-muda">{{ $tutor->id_tutor }}</span></td>
                                <td><x-dasbor.label :tone="$tutor->isInternal() ? 'info' : 'netral'">{{ $tutor->isInternal() ? 'Internal' : 'Eksternal' }}</x-dasbor.label></td>
                                <td>{{ $tutor->mataPelajaran->map->label()->join(', ') ?: '–' }}</td>
                                <td>{{ $tutor->profil?->lokasi ?? '–' }}</td>
                                <td class="angka">{{ $tutor->dokumen_count }} berkas</td>
                                <td class="whitespace-nowrap">{{ Format::waktu($tutor->diajukan_pada) }}</td>
                                <td><a href="{{ route('admin.verifikasi.show', $tutor) }}" class="tombol-garis min-h-10">Periksa<span class="sr-only"> {{ $tutor->nama }}</span></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-dasbor.panel>
</x-dasbor.layout>
