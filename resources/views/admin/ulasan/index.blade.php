@use('App\Support\Format')

<x-dasbor.layout title="Moderasi ulasan">
    <x-dasbor.judul title="Moderasi ulasan" note="ulasan yang disembunyikan tidak dihitung di rating tutor" />

    <nav aria-label="Saring ulasan" class="mb-5 flex flex-wrap gap-2">
        @foreach ([null => 'Semua', 'ya' => 'Ditampilkan', 'tidak' => 'Disembunyikan'] as $nilai => $teks)
            @php($aktif = ($tampil === null && $nilai === '') || ($tampil === true && $nilai === 'ya') || ($tampil === false && $nilai === 'tidak'))
            <a href="{{ route('admin.ulasan.index', array_filter(['tampil' => $nilai])) }}" @if ($aktif) aria-current="page" @endif
               @class(['inline-flex min-h-10 items-center rounded-full border-2 px-3.5 text-[0.9375rem] font-bold', 'border-tinta bg-tinta text-white' => $aktif, 'border-pensil/25 bg-white hover:border-tinta' => ! $aktif])>{{ $teks }}</a>
        @endforeach
    </nav>

    <x-dasbor.panel>
        @if ($daftarUlasan->isEmpty())
            <x-dasbor.kosong message="Belum ada ulasan di sini." />
        @else
            <ul class="divide-y divide-pensil/12">
                @foreach ($daftarUlasan as $ulasan)
                    <li class="grid gap-4 py-4 lg:grid-cols-[minmax(0,1fr)_22rem]">
                        <div>
                            <p class="flex flex-wrap items-center gap-2">
                                <span class="font-bold">{{ $ulasan->nilai }} dari 5 untuk {{ $ulasan->pesanan->tutor->nama }}</span>
                                <x-dasbor.label :tone="$ulasan->ditampilkan ? 'baik' : 'buruk'">{{ $ulasan->ditampilkan ? 'Ditampilkan' : 'Disembunyikan' }}</x-dasbor.label>
                            </p>
                            <p class="text-[0.875rem] text-pensil-muda">{{ $ulasan->id_ulasan }} · {{ $ulasan->pesanan->siswa->wali->nama }} · {{ Format::tanggal($ulasan->created_at) }} · <a href="{{ route('admin.pesanan.show', $ulasan->id_pesanan) }}" class="tautan">{{ $ulasan->id_pesanan }}</a></p>
                            <p class="mt-2">{{ $ulasan->komentar ?? 'Tanpa komentar.' }}</p>
                            @if ($ulasan->alasan_moderasi)
                                <p class="mt-2 text-[0.9375rem]"><span class="font-bold">Alasan moderasi terakhir:</span> {{ $ulasan->alasan_moderasi }}</p>
                            @endif
                        </div>
                        <form method="post" action="{{ route('admin.ulasan.update', $ulasan) }}" class="space-y-2" novalidate>
                            @csrf
                            @method('put')
                            <input type="hidden" name="ditampilkan" value="{{ $ulasan->ditampilkan ? 0 : 1 }}">
                            <label for="alasan-{{ $ulasan->id_ulasan }}" class="block text-[0.875rem] font-bold">Alasan {{ $ulasan->ditampilkan ? 'menyembunyikan' : 'menampilkan lagi' }}</label>
                            <input id="alasan-{{ $ulasan->id_ulasan }}" name="alasan" type="text" maxlength="255" class="kontrol" required>
                            <button type="submit" class="{{ $ulasan->ditampilkan ? 'tombol-garis tombol-bahaya' : 'tombol-garis' }}">{{ $ulasan->ditampilkan ? 'Sembunyikan' : 'Tampilkan lagi' }}</button>
                        </form>
                    </li>
                @endforeach
            </ul>
            @error('alasan')
                <p class="koreksi" role="alert">{{ $message }}</p>
            @enderror
            {{ $daftarUlasan->links('components.dasbor.halaman') }}
        @endif
    </x-dasbor.panel>
</x-dasbor.layout>
