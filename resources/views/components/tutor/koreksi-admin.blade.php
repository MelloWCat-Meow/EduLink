@props(['catatan' => null])

{{--
    The academic team's correction, written in the margin in red ink like a
    teacher's note. A heading and an icon say what it is, so colour is not the
    only signal.
--}}
<aside role="note" aria-label="Koreksi dari tim akademik" {{ $attributes->merge(['class' => 'relative rotate-[-0.8deg] border-l-4 border-cap bg-[rgb(192_38_45/0.05)] px-4 py-3']) }}>
    <p class="flex items-center gap-2 text-[0.875rem] font-bold tracking-wide text-cap uppercase">
        <svg aria-hidden="true" class="size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M13.5 3.5l3 3L7 16l-4 1 1-4z"/></svg>
        Koreksi tim akademik
    </p>
    <p class="mt-1 font-tangan text-[1.2rem] leading-snug text-cap">{{ filled($catatan) ? $catatan : 'Bagian ini perlu diperiksa ulang dan disimpan lagi.' }}</p>
</aside>
