@use('App\Enums\StatusVerifikasiTutor')

<x-dasbor.layout title="Kelola tutor">
    <x-dasbor.judul title="Kelola tutor" note="semua tutor: verifikasi, jenis, status akun, dan mata pelajaran" />

    <form method="get" action="{{ route('admin.tutor.index') }}" class="mb-5 grid grid-cols-[minmax(0,1fr)] gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.4fr)_repeat(3,minmax(0,1fr))_auto] lg:items-end">
        <label class="block"><span class="block text-[0.875rem] font-bold">Cari nama</span>
            <input type="search" name="cari" value="{{ $filter['cari'] }}" class="kontrol" placeholder="Nama tutor">
        </label>
        <label class="block"><span class="block text-[0.875rem] font-bold">Verifikasi</span>
            <select name="status" class="kontrol">
                <option value="">Semua</option>
                @foreach (StatusVerifikasiTutor::cases() as $status)
                    <option value="{{ $status->value }}" @selected($filter['status'] === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </label>
        <label class="block"><span class="block text-[0.875rem] font-bold">Jenis</span>
            <select name="jenis" class="kontrol">
                <option value="">Semua</option>
                <option value="internal" @selected($filter['jenis'] === 'internal')>Internal</option>
                <option value="eksternal" @selected($filter['jenis'] === 'eksternal')>Eksternal</option>
            </select>
        </label>
        <label class="block"><span class="block text-[0.875rem] font-bold">Akun</span>
            <select name="aktif" class="kontrol">
                <option value="">Semua</option>
                <option value="aktif" @selected($filter['aktif'] === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected($filter['aktif'] === 'nonaktif')>Nonaktif</option>
            </select>
        </label>
        <button type="submit" class="tombol">Saring</button>
    </form>

    <x-dasbor.panel>
        @if ($daftarTutor->isEmpty())
            <x-dasbor.kosong message="Tidak ada tutor yang cocok dengan saringan ini." />
        @else
            <div class="overflow-x-auto">
                <table class="tabel">
                    <thead><tr><th scope="col">Tutor</th><th scope="col">Jenis</th><th scope="col">Verifikasi</th><th scope="col">Akun</th><th scope="col" class="text-right">Pesanan</th><th scope="col">Rating</th><th scope="col"><span class="sr-only">Aksi</span></th></tr></thead>
                    <tbody>
                        @foreach ($daftarTutor as $tutor)
                            <tr>
                                <td><span class="font-bold">{{ $tutor->nama }}</span><span class="block text-[0.8125rem] text-pensil-muda">{{ $tutor->id_tutor }} · {{ $tutor->profil?->lokasi ?? 'kecamatan belum diisi' }}</span></td>
                                <td>{{ $tutor->isInternal() ? 'Internal' : 'Eksternal' }}</td>
                                <td><x-dasbor.label :tone="$tutor->status_verifikasi->tone()">{{ $tutor->status_verifikasi->label() }}</x-dasbor.label></td>
                                <td>
                                    @if (! $tutor->akun)
                                        <span class="text-pensil-muda">belum aktivasi</span>
                                    @else
                                        <x-dasbor.label :tone="$tutor->akun->aktif ? 'baik' : 'buruk'">{{ $tutor->akun->aktif ? 'Aktif' : 'Nonaktif' }}</x-dasbor.label>
                                    @endif
                                </td>
                                <td class="angka text-right">{{ $tutor->pesanan_count }}</td>
                                <td><x-tutor.rating :rata="$tutor->rata_rating" :jumlah="$tutor->jumlah_ulasan" /></td>
                                <td><a href="{{ route('admin.tutor.show', $tutor) }}" class="tombol-garis min-h-10">Kelola<span class="sr-only"> {{ $tutor->nama }}</span></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $daftarTutor->links('components.dasbor.halaman') }}
        @endif
    </x-dasbor.panel>
</x-dasbor.layout>
