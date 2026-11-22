<x-dasbor.layout :title="'Ubah data '.$siswa->nama">
    <x-dasbor.judul :title="'Ubah data '.$siswa->nama" />

    <x-dasbor.panel heading="Data anak" class="max-w-xl">
        @include('ortu.anak._form', ['action' => route('anak.update', $siswa), 'method' => 'put', 'siswa' => $siswa, 'tombol' => 'Simpan perubahan'])
        <a href="{{ route('anak.index') }}" class="tautan mt-5 inline-block">Kembali ke data anak</a>
    </x-dasbor.panel>
</x-dasbor.layout>
