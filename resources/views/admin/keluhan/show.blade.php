@use('App\Support\Format')
@use('App\Enums\StatusKeluhan')

<x-dasbor.layout :title="'Keluhan '.$keluhan->id_keluhan">
    <a href="{{ route('admin.keluhan.index') }}" class="tautan mb-4 inline-block">Kembali ke daftar keluhan</a>
    <x-dasbor.judul :title="'Keluhan '.$keluhan->id_keluhan" :note="$keluhan->jenis->label()">
        <x-slot:actions><x-dasbor.label :tone="$keluhan->status->tone()" class="text-[1rem]">{{ $keluhan->status->label() }}</x-dasbor.label></x-slot:actions>
    </x-dasbor.judul>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)] lg:items-start">
        <x-dasbor.panel heading="Isi keluhan">
            <dl class="grid gap-x-6 gap-y-3 sm:grid-cols-2">
                <div><dt class="text-[0.875rem] text-pensil-muda">Pengaju</dt><dd class="font-bold">{{ $keluhan->pengaju->nama }} ({{ $keluhan->pengaju->kontak }}) <x-dasbor.label :tone="$keluhan->pengaju->peran === \App\Enums\Peran::Tutor ? 'info' : 'netral'">{{ $keluhan->pengaju->peran->label() }}</x-dasbor.label></dd></div>
                <div><dt class="text-[0.875rem] text-pensil-muda">Masuk</dt><dd class="font-bold">{{ Format::waktu($keluhan->created_at) }}</dd></div>
                <div><dt class="text-[0.875rem] text-pensil-muda">Pesanan</dt><dd><a href="{{ route('admin.pesanan.show', $keluhan->id_pesanan) }}" class="tautan">{{ $keluhan->id_pesanan }}</a></dd></div>
                <div><dt class="text-[0.875rem] text-pensil-muda">Tutor dan mapel</dt><dd class="font-bold">{{ $keluhan->pesanan->tutor->nama }}, {{ $keluhan->pesanan->mapel->label() }}</dd></div>
            </dl>
            <p class="mt-5 border-t-2 border-dashed border-pensil/25 pt-4">{{ $keluhan->deskripsi }}</p>
        </x-dasbor.panel>

        <x-dasbor.panel heading="Tanggapan">
            @if ($keluhan->status === StatusKeluhan::Selesai)
                <p class="bg-langit/50 px-3 py-2">{{ $keluhan->tanggapan_admin }}</p>
                <p class="mt-2 text-[0.875rem] text-pensil-muda">Keluhan sudah selesai. Diperbarui {{ Format::waktu($keluhan->updated_at) }}.</p>
            @else
                <form method="post" action="{{ route('admin.keluhan.update', $keluhan) }}" class="space-y-5" novalidate>
                    @csrf
                    @method('put')
                    <x-dasbor.field name="status" label="Status">
                        <select id="isian-status" name="status" class="kontrol" @error('status') aria-invalid="true" aria-describedby="isian-status-galat" @enderror>
                            @foreach (StatusKeluhan::cases() as $status)
                                @if ($status === $keluhan->status || $keluhan->status->canMoveTo($status))
                                    <option value="{{ $status->value }}" @selected(old('status', $keluhan->status === StatusKeluhan::Baru ? 'diproses' : 'selesai') === $status->value)>{{ $status->label() }}</option>
                                @endif
                            @endforeach
                        </select>
                    </x-dasbor.field>
                    <x-dasbor.field name="tanggapan_admin" label="Tanggapan untuk pengaju" hint="Terlihat oleh pengaju di halaman Keluhan miliknya.">
                        <textarea id="isian-tanggapan_admin" name="tanggapan_admin" maxlength="2000" class="kontrol" aria-describedby="isian-tanggapan_admin-petunjuk @error('tanggapan_admin') isian-tanggapan_admin-galat @enderror" @error('tanggapan_admin') aria-invalid="true" @enderror>{{ old('tanggapan_admin', $keluhan->tanggapan_admin) }}</textarea>
                    </x-dasbor.field>
                    <button type="submit" class="tombol">Simpan tanggapan</button>
                </form>
            @endif
        </x-dasbor.panel>
    </div>
</x-dasbor.layout>
