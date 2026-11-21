<x-dasbor.layout title="Akun saya">
    <x-dasbor.judul title="Akun saya" :note="'admin · '.$akun->kontak" />

    <div class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:grid-cols-2 lg:items-start">
        <x-dasbor.panel heading="Nama">
            <form method="POST" action="{{ route('admin.akun.update') }}" novalidate class="space-y-4">
                @csrf
                @method('PUT')
                <x-dasbor.field name="nama" label="Nama lengkap">
                    <input id="isian-nama" name="nama" value="{{ old('nama', $akun->nama) }}" maxlength="100" autocomplete="name" class="kontrol" @error('nama') aria-invalid="true" aria-describedby="isian-nama-galat" @enderror>
                </x-dasbor.field>
                <button type="submit" class="tombol">Simpan nama</button>
            </form>
        </x-dasbor.panel>

        <x-dasbor.form-kata-sandi />
    </div>
</x-dasbor.layout>
