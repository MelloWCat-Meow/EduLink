@use('App\Support\Format')
@use('App\Enums\ModeBelajar')
@use('App\Enums\Kehadiran')

<x-dasbor.layout title="Sesi">
    <x-dasbor.judul title="Sesi" note="catat sesi setelah dimulai; orang tua mengonfirmasi dalam 48 jam, lalu bagian Anda masuk saldo" />

    <div class="space-y-8">
        <x-dasbor.panel heading="Perlu dicatat">
            @if ($perluDicatat->isEmpty())
                <x-dasbor.kosong message="Tidak ada sesi yang menunggu dicatat." />
            @else
                <ul class="divide-y divide-pensil/12">
                    @foreach ($perluDicatat as $sesi)
                        @php($idForm = 'catat-'.$sesi->id_sesi)
                        <li id="sesi-{{ $sesi->id_sesi }}" class="grid grid-cols-[minmax(0,1fr)] gap-3 py-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.3fr)] lg:gap-6">
                            <div>
                                <p class="font-bold">{{ Format::waktu($sesi->waktu, true) }}</p>
                                <p class="text-[0.9375rem]">{{ $sesi->pesanan->mapel->label() }} · {{ $sesi->pesanan->siswa->nama }}</p>
                                <a href="{{ route('tutor.pesanan.show', $sesi->id_pesanan) }}" class="tautan text-[0.875rem]">{{ $sesi->id_pesanan }}</a>
                            </div>
                            <form method="post" action="{{ route('tutor.sesi.catat', $sesi) }}" class="space-y-3" novalidate>
                                @csrf
                                <fieldset>
                                    <legend class="font-bold">Bagaimana sesinya?</legend>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($pilihanKehadiran as $pilihan)
                                            <label class="flex min-h-11 items-center gap-2 rounded-md border-2 border-pensil/20 px-3 has-[:checked]:border-tinta has-[:checked]:bg-langit/50">
                                                <input type="radio" name="kehadiran" value="{{ $pilihan->value }}" class="size-4 accent-tinta" @checked($pilihan === Kehadiran::Hadir)>
                                                {{ $pilihan->label() }}
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                                <label for="{{ $idForm }}-catatan" class="block text-[0.9375rem] font-bold">Catatan materi <span class="font-normal text-pensil-muda">(dibaca orang tua)</span></label>
                                <textarea id="{{ $idForm }}-catatan" name="catatan" rows="2" maxlength="1000" class="kontrol" placeholder="Materi yang dibahas, PR, atau hal yang perlu diulang."></textarea>
                                <p class="text-[0.875rem] text-pensil-muda">"Siswa tidak hadir" berarti Anda datang atau membuka ruang meeting, tetapi siswa tidak hadir tanpa kabar; sesi tetap dihitung terlaksana. Kalau Anda yang berhalangan, jangan catat sesi ini: ajukan jadwal ulang dari <a href="{{ route('tutor.pesanan.show', $sesi->id_pesanan) }}" class="tautan">detail pesanan</a>.</p>
                                <button type="submit" class="tombol">Simpan catatan<span class="sr-only"> sesi {{ $sesi->id_sesi }}</span></button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-dasbor.panel>

        <x-dasbor.panel heading="Mendatang">
            @if ($mendatang->isEmpty())
                <x-dasbor.kosong message="Belum ada sesi mendatang." />
            @else
                <div class="overflow-x-auto">
                    <table class="tabel">
                        <thead><tr><th scope="col">Waktu</th><th scope="col">Mapel</th><th scope="col">Siswa</th><th scope="col">Tempat</th></tr></thead>
                        <tbody>
                            @foreach ($mendatang as $sesi)
                                <tr>
                                    <td class="whitespace-nowrap">{{ Format::waktu($sesi->waktu, true) }}</td>
                                    <td>{{ $sesi->pesanan->mapel->label() }}</td>
                                    <td>{{ $sesi->pesanan->siswa->nama }}</td>
                                    <td>
                                        <x-dasbor.tautan-meeting :sesi="$sesi" :pesanan="$sesi->pesanan" untuk-tutor />
                                        @if ($sesi->pesanan->mode === ModeBelajar::Online && ! $sesi->tautanBerlaku())
                                            <a href="{{ route('tutor.pesanan.show', $sesi->id_pesanan) }}#tautan-meeting" class="tautan block">Isi tautan</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-dasbor.panel>

        <x-dasbor.panel heading="Sudah dicatat">
            @if ($tercatat->isEmpty())
                <x-dasbor.kosong message="Belum ada sesi yang dicatat." />
            @else
                <div class="overflow-x-auto">
                    <table class="tabel">
                        <thead><tr><th scope="col">Waktu</th><th scope="col">Mapel dan siswa</th><th scope="col">Status</th><th scope="col">Catatan</th></tr></thead>
                        <tbody>
                            @foreach ($tercatat as $sesi)
                                <tr>
                                    <td class="whitespace-nowrap">{{ Format::waktu($sesi->waktu) }}</td>
                                    <td>{{ $sesi->pesanan->mapel->label() }} · {{ $sesi->pesanan->siswa->nama }}</td>
                                    <td><x-dasbor.status-sesi :sesi="$sesi" /></td>
                                    <td class="min-w-[14rem]">{{ $sesi->catatan ?: '–' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $tercatat->links('components.dasbor.halaman') }}
            @endif
        </x-dasbor.panel>
    </div>
</x-dasbor.layout>
