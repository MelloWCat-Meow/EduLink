@use('App\Enums\StatusJadwal')

<x-dasbor.layout title="Jadwal">
    <x-dasbor.judul title="Jadwal ketersediaan" note="slot mingguan yang bisa dipesan orang tua" />

    <div class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] lg:items-start">
        <x-dasbor.panel heading="Slot Anda">
            @if ($daftarJadwal->isEmpty())
                <x-dasbor.kosong message="Belum ada slot. Tambahkan minimal satu supaya orang tua bisa memesan." />
            @else
                <ul class="divide-y divide-pensil/12">
                    @foreach ($daftarJadwal as $slot)
                        @php($bebas = $slot->status === StatusJadwal::Tersedia)
                        <li class="py-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <p class="font-display text-[1.15rem] font-extrabold">{{ $slot->label() }}</p>
                                <x-dasbor.label :tone="$slot->status->tone()">{{ $slot->status->label() }}</x-dasbor.label>
                            </div>
                            @if ($bebas)
                                <div class="mt-3 flex flex-wrap items-end gap-3">
                                    <form method="post" action="{{ route('tutor.jadwal.update', $slot) }}" class="flex flex-wrap items-end gap-2">
                                        @csrf
                                        @method('PUT')
                                        <label class="text-[0.875rem]"><span class="block font-bold">Hari</span>
                                            <select name="hari" class="kontrol w-36">
                                                @foreach ($pilihanHari as $hari)
                                                    <option value="{{ $hari->value }}" @selected($slot->hari === $hari)>{{ $hari->label() }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="text-[0.875rem]"><span class="block font-bold">Jam</span>
                                            <input type="time" name="jam" value="{{ substr($slot->jam, 0, 5) }}" min="06:00" max="21:00" step="900" class="kontrol w-32">
                                        </label>
                                        <button type="submit" class="tombol-garis">Ubah<span class="sr-only"> slot {{ $slot->label() }}</span></button>
                                    </form>
                                    <form method="post" action="{{ route('tutor.jadwal.destroy', $slot) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tombol-garis tombol-bahaya">Hapus<span class="sr-only"> slot {{ $slot->label() }}</span></button>
                                    </form>
                                </div>
                            @else
                                <p class="mt-2 text-[0.9375rem] text-pensil-muda">
                                    {{ $slot->status === StatusJadwal::Ditahan
                                        ? 'Sedang ditahan pesanan yang menunggu jawaban atau pembayaran, jadi belum bisa diubah atau dihapus.'
                                        : 'Terisi pesanan yang sedang berjalan, jadi belum bisa diubah atau dihapus sampai pesanan itu selesai.' }}
                                </p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-dasbor.panel>

        <x-dasbor.panel heading="Tambah slot">
            <form method="post" action="{{ route('tutor.jadwal.store') }}" class="space-y-4" novalidate>
                @csrf
                <x-dasbor.field name="hari" label="Hari">
                    <select id="isian-hari" name="hari" class="kontrol" @error('hari') aria-invalid="true" aria-describedby="isian-hari-galat" @enderror>
                        <option value="">Pilih hari</option>
                        @foreach ($pilihanHari as $hari)
                            <option value="{{ $hari->value }}" @selected(old('hari') === $hari->value)>{{ $hari->label() }}</option>
                        @endforeach
                    </select>
                </x-dasbor.field>
                <x-dasbor.field name="jam" label="Jam mulai" hint="Antara 06:00 dan 21:00 WIB.">
                    <input id="isian-jam" type="time" name="jam" value="{{ old('jam') }}" min="06:00" max="21:00" step="900" class="kontrol max-w-[10rem]" aria-describedby="isian-jam-petunjuk @error('jam') isian-jam-galat @enderror" @error('jam') aria-invalid="true" @enderror>
                </x-dasbor.field>
                <button type="submit" class="tombol">Tambah slot</button>
            </form>
        </x-dasbor.panel>
    </div>
</x-dasbor.layout>
