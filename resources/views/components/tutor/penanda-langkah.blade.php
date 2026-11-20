@props(['langkah', 'tutor' => null, 'kekurangan' => []])

@use('App\Enums\LangkahPendaftaran')

{{--
    Numbered step marker of the application sheet. Each step says in text
    whether it is done, missing something, or flagged by the admin, so the
    state never relies on colour. A visitor (no account yet) cannot jump ahead.
--}}
@php
    $semua = LangkahPendaftaran::cases();
    $keadaan = function (LangkahPendaftaran $item) use ($tutor, $kekurangan): string {
        if ($tutor === null || $item === LangkahPendaftaran::Kirim) {
            return 'kosong';
        }
        if ($tutor->perluDiperbaiki($item)) {
            return 'perbaiki';
        }

        return array_key_exists($item->value, $kekurangan) ? 'kurang' : 'lengkap';
    };
    $teks = ['lengkap' => 'lengkap', 'kurang' => 'belum lengkap', 'perbaiki' => 'perlu diperbaiki', 'kosong' => ''];
@endphp

<nav aria-label="Langkah pendaftaran" {{ $attributes->merge(['class' => 'mb-8']) }}>
    <p class="mb-3 font-tangan text-[1.2rem] text-pensil-muda sm:hidden">
        Langkah {{ $langkah->nomor() }} dari {{ count($semua) }}: <span class="font-bold text-tinta">{{ $langkah->label() }}</span>
    </p>

    <ol class="grid grid-cols-5 gap-1 sm:gap-3">
        @foreach ($semua as $item)
            @php
                $kini = $item === $langkah;
                $k = $keadaan($item);
                $bisaDibuka = $tutor !== null && ! $kini;
            @endphp
            <li class="relative">
                <x-dynamic-component :component="$bisaDibuka ? 'tutor.tautan-langkah' : 'tutor.teks-langkah'" :href="$bisaDibuka ? route('tutor.pendaftaran.langkah', $item) : null" :kini="$kini"
                    class="group flex min-h-14 flex-col items-center gap-1 rounded-md px-1 py-1 text-center sm:flex-row sm:items-start sm:gap-2 sm:text-left">
                    <span @class([
                        'grid size-9 shrink-0 place-items-center rounded-full border-2 font-display text-[1.05rem] font-extrabold',
                        'border-tinta bg-tinta text-white' => $kini,
                        'border-pensil bg-mint' => ! $kini && $k === 'lengkap',
                        'border-cap bg-white text-cap' => ! $kini && $k === 'perbaiki',
                        'border-pensil/40 bg-white text-pensil' => ! $kini && in_array($k, ['kurang', 'kosong'], true),
                    ])>
                        @if (! $kini && $k === 'lengkap')
                            <svg aria-hidden="true" class="size-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg>
                        @elseif (! $kini && $k === 'perbaiki')
                            !
                        @else
                            {{ $item->nomor() }}
                        @endif
                    </span>
                    <span class="hidden min-w-0 sm:block">
                        <span @class(['block font-bold leading-tight', 'stabilo' => $kini])>{{ $item->label() }}</span>
                        @if ($teks[$k])
                            <span @class(['block text-[0.8125rem] leading-tight', 'font-bold text-cap' => $k === 'perbaiki', 'text-pensil-muda' => $k !== 'perbaiki'])>{{ $teks[$k] }}</span>
                        @endif
                    </span>
                    <span class="sr-only sm:hidden">Langkah {{ $item->nomor() }}: {{ $item->label() }}{{ $teks[$k] ? ', '.$teks[$k] : '' }}</span>
                </x-dynamic-component>
            </li>
        @endforeach
    </ol>
</nav>
