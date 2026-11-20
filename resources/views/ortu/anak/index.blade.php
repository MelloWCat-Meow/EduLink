<x-dasbor.layout title="Data anak">
    <x-dasbor.judul title="Data anak" note="kontak wali diisi otomatis dari email akun Anda" />

    <div class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] lg:items-start">
        <x-dasbor.panel heading="Anak yang terdaftar">
            @if ($daftarAnak->isEmpty())
                <x-dasbor.kosong message="Belum ada data anak. Tambahkan lewat formulir di samping." />
            @else
                <ul>
                    @foreach ($daftarAnak as $anak)
                        <li class="flex flex-wrap items-center justify-between gap-x-6 gap-y-3 border-b border-pensil/12 py-3.5 last:border-0">
                            <div>
                                <p class="font-bold">{{ $anak->nama }} <span class="ml-1 font-normal text-pensil-muda">· {{ $anak->jenjang }}</span></p>
                                <p class="text-[0.9375rem] text-pensil-muda">
                                    {{ $anak->id_siswa }} · {{ $anak->pesanan_count }} pesanan
                                    @if ($anak->pesanan_aktif_count > 0)
                                        , {{ $anak->pesanan_aktif_count }} masih berjalan
                                    @endif
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('anak.edit', $anak) }}" class="tombol-garis min-h-10 text-[0.9375rem]">Ubah<span class="sr-only"> data {{ $anak->nama }}</span></a>
                                @if ($anak->pesanan_count === 0)
                                    <form method="post" action="{{ route('anak.destroy', $anak) }}" onsubmit="return confirm('Hapus data {{ $anak->nama }}?')">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="tombol-garis tombol-bahaya min-h-10 text-[0.9375rem]">Hapus<span class="sr-only"> data {{ $anak->nama }}</span></button>
                                    </form>
                                @else
                                    <p class="max-w-[14rem] text-[0.8125rem] leading-snug text-pensil-muda">
                                        {{ $anak->pesanan_aktif_count > 0 ? 'Tidak bisa dihapus selama ada pesanan berjalan.' : 'Disimpan karena punya riwayat pesanan.' }}
                                    </p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-dasbor.panel>

        <x-dasbor.panel heading="Tambah anak">
            @include('ortu.anak._form', ['action' => route('anak.store'), 'method' => null, 'siswa' => null, 'tombol' => 'Tambah anak'])
        </x-dasbor.panel>
    </div>
</x-dasbor.layout>
