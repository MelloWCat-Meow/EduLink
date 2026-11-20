@props(['levels'])

<section id="mapel" aria-labelledby="judul-mapel" class="kertas-kotak sobek-atas halaman relative -mt-4 pt-20 pb-20 lg:pt-28 lg:pb-28">
    <div class="mx-auto max-w-[78rem]">
        <h2 id="judul-mapel" class="max-w-[20ch] font-display text-[clamp(2rem,5vw,3.6rem)] leading-[1.02] font-extrabold tracking-tight [font-stretch:88%]">
            Langsung ke mapel yang dicari
        </h2>
        <p class="mt-4 max-w-[36rem] text-[1.0625rem] leading-[1.75rem]">
            Mapel yang paling sering dicari orang tua untuk tiap jenjang. Pilih satu untuk melihat tutornya.
        </p>

        <div class="mt-10 grid border-2 border-pensil bg-white md:grid-cols-3">
            @foreach ($levels as $key => $level)
                <div @class(['p-5 sm:p-6', 'border-t-2 border-pensil md:border-t-0 md:border-l-2' => ! $loop->first])>
                    <h3 class="flex items-baseline justify-between gap-3 border-b-2 border-pensil pb-3">
                        <span class="font-display text-[2rem] leading-none font-extrabold [font-stretch:85%]">{{ $level['label'] }}</span>
                        <span class="font-tangan text-[1.1rem] text-pensil-muda">{{ $level['grades'] }}</span>
                    </h3>
                    <ul class="mt-3">
                        @foreach ($level['subjects'] as $subject)
                            <li class="border-b border-dashed border-garis last:border-0">
                                <a href="{{ route('tutor.index', ['mapel' => $subject, 'jenjang' => $key]) }}" class="flex min-h-12 items-center">
                                    <span class="chip-mapel text-[1.0625rem] font-bold">{{ $subject }}</span>
                                    <span class="sr-only">untuk {{ $level['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('tutor.index', ['jenjang' => $key]) }}" class="mt-3 inline-flex min-h-11 items-center font-tangan text-[1.2rem] text-tinta underline decoration-2 underline-offset-4">
                        semua tutor {{ $level['label'] }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
