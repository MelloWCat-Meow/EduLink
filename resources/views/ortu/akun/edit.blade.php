<x-dasbor.layout title="Akun saya">
    <x-dasbor.judul title="Akun saya" note="nama dan nomor HP yang dilihat tutor setelah pesanan dibayar" />

    <x-dasbor.panel class="max-w-[38rem]">
        <form method="POST" action="{{ route('akun.update') }}" novalidate class="space-y-5">
            @csrf
            @method('PUT')
            <x-dasbor.field name="nama" label="Nama lengkap">
                <input id="isian-nama" name="nama" value="{{ old('nama', $akun->nama) }}" maxlength="100" autocomplete="name" class="kontrol" @error('nama') aria-invalid="true" aria-describedby="isian-nama-galat" @enderror>
            </x-dasbor.field>
            <x-dasbor.field name="no_hp" label="Nomor HP (WhatsApp)" hint="Tutor hanya bisa menghubungi nomor ini untuk pesanan yang sudah dibayar.">
                <input id="isian-no_hp" name="no_hp" type="tel" inputmode="tel" autocomplete="tel" value="{{ old('no_hp', $akun->no_hp) }}" maxlength="20" class="kontrol max-w-[16rem]"
                    aria-describedby="isian-no_hp-petunjuk @error('no_hp') isian-no_hp-galat @enderror" @error('no_hp') aria-invalid="true" @enderror>
            </x-dasbor.field>
            <div>
                <p class="font-bold">Email</p>
                <p>{{ $akun->kontak }}</p>
                <p class="text-[0.875rem] text-pensil-muda">Email dipakai untuk masuk, jadi belum bisa diubah.</p>
            </div>
            <button type="submit" class="tombol">Simpan</button>
        </form>
    </x-dasbor.panel>
</x-dasbor.layout>
