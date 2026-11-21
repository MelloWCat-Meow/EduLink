{{-- Change password while signed in. Errors live in their own "kataSandi" bag. --}}
@php($galat = $errors->getBag('kataSandi'))

<x-dasbor.panel heading="Ganti kata sandi" heading-id="kata-sandi" {{ $attributes }}>
    <form method="POST" action="{{ route('kata-sandi.ganti') }}" novalidate class="space-y-4">
        @csrf
        @method('PUT')
        @foreach (['kata_sandi_lama' => ['Kata sandi lama', 'current-password'], 'password' => ['Kata sandi baru', 'new-password'], 'password_confirmation' => ['Ulangi kata sandi baru', 'new-password']] as $nama => [$label, $isiOtomatis])
            <div>
                <label for="sandi-{{ $nama }}" class="block font-bold">{{ $label }}</label>
                <input id="sandi-{{ $nama }}" name="{{ $nama }}" type="password" autocomplete="{{ $isiOtomatis }}" class="kontrol mt-1.5 max-w-[22rem]"
                    @if ($galat->has($nama)) aria-invalid="true" aria-describedby="sandi-{{ $nama }}-galat" @endif>
                @if ($galat->has($nama))
                    <p id="sandi-{{ $nama }}-galat" class="koreksi"><span><span class="sr-only">Perlu dibetulkan: </span>{{ $galat->first($nama) }}</span></p>
                @endif
            </div>
        @endforeach
        <p class="text-[0.875rem] text-pensil-muda">Minimal 8 karakter. Perangkat lain yang masih masuk akan dikeluarkan.</p>
        <button type="submit" class="tombol-garis">Ganti kata sandi</button>
    </form>
</x-dasbor.panel>
