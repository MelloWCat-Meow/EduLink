<form method="post" action="{{ $action }}" class="space-y-5" novalidate>
    @csrf
    @if ($method)
        @method($method)
    @endif

    <x-dasbor.field name="nama" label="Nama anak" hint="Nama panggilan juga boleh.">
        <input id="isian-nama" name="nama" type="text" maxlength="100" autocomplete="off" required
               value="{{ old('nama', $siswa?->nama) }}" class="kontrol"
               aria-describedby="isian-nama-petunjuk @error('nama') isian-nama-galat @enderror"
               @error('nama') aria-invalid="true" @enderror>
    </x-dasbor.field>

    <x-dasbor.field name="jenjang" label="Jenjang sekolah">
        <select id="isian-jenjang" name="jenjang" class="kontrol" required @error('jenjang') aria-invalid="true" aria-describedby="isian-jenjang-galat" @enderror>
            <option value="">Pilih jenjang</option>
            @foreach (['SD' => 'SD (kelas 1–6)', 'SMP' => 'SMP (kelas 7–9)', 'SMA' => 'SMA (kelas 10–12)'] as $nilai => $teks)
                <option value="{{ $nilai }}" @selected(old('jenjang', $siswa?->jenjang) === $nilai)>{{ $teks }}</option>
            @endforeach
        </select>
    </x-dasbor.field>

    <button type="submit" class="tombol">{{ $tombol }}</button>
</form>
