{{-- Change password while signed in. Errors live in their own "kataSandi" bag. --}}
@php($galat = $errors->getBag('kataSandi'))

<x-dasbor.panel heading="Ganti kata sandi" heading-id="kata-sandi" {{ $attributes }}>
    <form method="POST" action="{{ route('kata-sandi.ganti') }}" novalidate class="space-y-4">
        @csrf
        @method('PUT')
        @foreach (['kata_sandi_lama' => ['Kata sandi lama', 'current-password'], 'password' => ['Kata sandi baru', 'new-password'], 'password_confirmation' => ['Ulangi kata sandi baru', 'new-password']] as $nama => [$label, $isiOtomatis])
            <x-dasbor.field :name="$nama" :for="'sandi-'.$nama" :label="$label" sandi bag="kataSandi" class="max-w-[22rem]">
                <input id="sandi-{{ $nama }}" name="{{ $nama }}" type="password" autocomplete="{{ $isiOtomatis }}" class="kontrol"
                    @if ($galat->has($nama)) aria-invalid="true" aria-describedby="sandi-{{ $nama }}-galat" @endif>
            </x-dasbor.field>
        @endforeach
        <p class="text-[0.875rem] text-pensil-muda">Minimal 8 karakter. Perangkat lain yang masih masuk akan dikeluarkan.</p>
        <button type="submit" class="tombol-garis">Ganti kata sandi</button>
    </form>
</x-dasbor.panel>
