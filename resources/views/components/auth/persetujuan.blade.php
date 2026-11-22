{{-- Required agreement on every sign-up form. Both documents open in a new tab so the form is not lost. --}}
<div>
    <label for="isian-setuju" class="flex items-start gap-3">
        <input id="isian-setuju" type="checkbox" name="setuju" value="1" class="centang mt-0.5" @checked(old('setuju'))
            @error('setuju') aria-invalid="true" aria-describedby="isian-setuju-galat" @enderror>
        <span class="leading-snug">
            Saya menyetujui
            <a href="{{ route('syarat') }}" target="_blank" rel="noopener" class="tautan">Syarat dan Ketentuan</a>
            serta
            <a href="{{ route('privacy') }}" target="_blank" rel="noopener" class="tautan">Kebijakan Privasi</a>
            EduLink Tutor.
            <span class="sr-only">(tautan membuka tab baru)</span>
        </span>
    </label>
    <x-dasbor.koreksi name="setuju" />
</div>
