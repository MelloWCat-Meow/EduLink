@use('App\Support\Format')
@use('App\Enums\Peran')

<x-dasbor.layout title="Data pengguna">
    <x-dasbor.judul title="Data pengguna" note="akun orang tua dan tutor; buka detail untuk menonaktifkan atau mengaktifkan" />

    <nav aria-label="Saring menurut peran" class="mb-5 flex flex-wrap gap-2">
        @foreach ([null => 'Semua', 'ortu' => 'Orang tua', 'tutor' => 'Tutor'] as $nilai => $label)
            @php($aktif = ($peranDipilih?->value) === ($nilai ?: null))
            <a href="{{ route('admin.pengguna.index', $nilai ? ['peran' => $nilai] : []) }}" @if ($aktif) aria-current="page" @endif @class(['tombol-garis min-h-10', 'bg-langit' => $aktif])>{{ $label }}</a>
        @endforeach
    </nav>

    <x-dasbor.panel>
        @if ($daftarAkun->isEmpty())
            <x-dasbor.kosong message="Belum ada akun." />
        @else
            <div class="overflow-x-auto">
                <table class="tabel">
                    <thead><tr><th scope="col">ID akun</th><th scope="col">Nama</th><th scope="col">Peran</th><th scope="col">Email</th><th scope="col" class="text-right">Anak</th><th scope="col">Status</th><th scope="col">Terdaftar</th><th scope="col"><span class="sr-only">Aksi</span></th></tr></thead>
                    <tbody>
                        @foreach ($daftarAkun as $akun)
                            <tr>
                                <td class="angka">{{ $akun->id_akun }}</td>
                                <td class="font-bold">{{ $akun->nama }}</td>
                                <td>{{ $akun->peran->label() }}</td>
                                <td>{{ $akun->kontak }}</td>
                                <td class="angka text-right">{{ $akun->hasPeran(Peran::Ortu) ? $akun->siswa_count : '–' }}</td>
                                <td><x-dasbor.label :tone="$akun->aktif ? 'baik' : 'buruk'">{{ $akun->aktif ? 'Aktif' : 'Nonaktif' }}</x-dasbor.label></td>
                                <td class="whitespace-nowrap">{{ Format::tanggalPendek($akun->created_at) }}</td>
                                <td class="whitespace-nowrap">
                                    <a href="{{ route('admin.pengguna.show', $akun) }}" class="tombol-garis min-h-10">{{ $akun->aktif ? 'Detail / nonaktifkan' : 'Detail / aktifkan' }}<span class="sr-only"> {{ $akun->nama }}</span></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $daftarAkun->links('components.dasbor.halaman') }}
        @endif
    </x-dasbor.panel>
</x-dasbor.layout>
