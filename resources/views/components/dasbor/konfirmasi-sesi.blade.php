@props(['sesi'])

@use('App\Enums\StatusKonfirmasiSesi')
@use('App\Support\Format')
@use('Illuminate\Support\MessageBag')

{{--
    For the parent: confirm or dispute a logged session within 48 hours,
    and see how a dispute was settled.
--}}
@php($galat = old('untuk_sesi') === $sesi->id_sesi ? $errors->getBag('sanggahan') : new MessageBag)

<div {{ $attributes->merge(['class' => 'text-[0.9375rem]']) }}>
    @if ($sesi->status_konfirmasi === StatusKonfirmasiSesi::MenungguKonfirmasi)
        <div class="mt-2 border-l-4 border-stabilo bg-sticky/60 px-3 py-3">
            <p><span class="font-bold">Apakah sesi ini berlangsung sesuai catatan tutor?</span> Jawab sebelum {{ Format::waktu($sesi->batasKonfirmasi()) }}.</p>
            <x-dasbor.hitung-mundur :batas="$sesi->batasKonfirmasi()" awalan="Sisa waktu" class="mt-1" />
            <div class="mt-3 flex flex-wrap items-start gap-3">
                <form method="post" action="{{ route('sesi.konfirmasi', $sesi) }}">
                    @csrf
                    <button type="submit" class="tombol min-h-11">Konfirmasi sesi</button>
                </form>
                <details class="group" @if ($galat->isNotEmpty()) open @endif>
                    <summary class="tombol-garis tombol-bahaya min-h-11 list-none">Sanggah</summary>
                    <form method="post" action="{{ route('sesi.sanggah', $sesi) }}" class="mt-2 max-w-[30rem] space-y-2" novalidate>
                        @csrf
                        <input type="hidden" name="untuk_sesi" value="{{ $sesi->id_sesi }}">
                        <label for="sanggah-{{ $sesi->id_sesi }}" class="block font-bold">Alasan sanggahan</label>
                        <textarea id="sanggah-{{ $sesi->id_sesi }}" name="alasan" rows="2" maxlength="255" class="kontrol" placeholder="Misalnya: tutor tidak datang, atau materi tidak sesuai catatan."
                            @if ($galat->has('alasan')) aria-invalid="true" aria-describedby="sanggah-{{ $sesi->id_sesi }}-galat" @endif>{{ $galat->isNotEmpty() ? old('alasan') : '' }}</textarea>
                        @if ($galat->has('alasan'))
                            <p id="sanggah-{{ $sesi->id_sesi }}-galat" class="koreksi"><span><span class="sr-only">Perlu dibetulkan: </span>{{ $galat->first('alasan') }}</span></p>
                        @endif
                        <p class="text-pensil-muda">Sanggahan menjadi keluhan yang diputuskan admin. Kalau sesi dinyatakan tidak sah, biaya sesi ini dikembalikan.</p>
                        <button type="submit" class="tombol-garis tombol-bahaya min-h-11">Kirim sanggahan</button>
                    </form>
                </details>
            </div>
            <p class="mt-2 text-pensil-muda">Tanpa jawaban sampai batas waktu, sesi dikonfirmasi otomatis.</p>
        </div>
    @elseif ($sesi->alasan_sanggahan)
        <p class="mt-1"><span class="font-bold">Sanggahan Anda:</span> {{ $sesi->alasan_sanggahan }}</p>
        @if ($sesi->keputusan_admin)
            <p><span class="font-bold">Keputusan admin:</span> {{ $sesi->keputusan_admin }}</p>
        @endif
    @endif
</div>
