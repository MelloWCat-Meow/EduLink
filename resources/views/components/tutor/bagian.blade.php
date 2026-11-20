@props(['nomor', 'judul', 'catatan' => null, 'perbaiki' => false, 'id' => null])

{{--
    One numbered section of the application sheet. When the admin flagged it,
    the red correction comes first on phones (read before the fields) and sits
    in the right margin on wide screens.
--}}
@php($idJudul = ($id ?? 'bagian-'.$nomor).'-judul')

<section @if ($id) id="{{ $id }}" @endif aria-labelledby="{{ $idJudul }}" {{ $attributes->merge(['class' => 'grid gap-5 lg:grid-cols-[minmax(0,1fr)_15rem] lg:gap-8']) }}>
    @if ($perbaiki)
        <x-tutor.koreksi-admin :catatan="$catatan" class="lg:order-2 lg:mt-14 lg:self-start" />
    @endif

    <div class="min-w-0 lg:order-1">
        <h2 id="{{ $idJudul }}" class="flex items-baseline gap-3 border-b-2 border-pensil pb-2 font-display text-[1.45rem] font-extrabold tracking-tight [font-stretch:88%]">
            <span class="font-tangan text-[1.5rem] font-bold text-tinta">{{ $nomor }}.</span>
            {{ $judul }}
        </h2>
        <div class="mt-6">{{ $slot }}</div>
    </div>
</section>
