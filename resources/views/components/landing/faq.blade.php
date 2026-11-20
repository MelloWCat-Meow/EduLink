@props(['faqs'])

<section id="tanya-jawab" aria-labelledby="judul-tanya-jawab" class="halaman pt-8 pb-20 lg:pb-28">
    <div class="mx-auto grid max-w-[78rem] gap-8 lg:grid-cols-12">
        <div class="lg:col-span-4">
            <h2 id="judul-tanya-jawab" class="font-display text-[clamp(2rem,5vw,3.6rem)] leading-[1.02] font-extrabold tracking-tight [font-stretch:88%]">
                Tanya jawab
            </h2>
            <p class="mt-4 max-w-[28rem] text-[1.0625rem] leading-[1.75rem]">
                Pertanyaan yang paling sering diajukan orang tua sebelum memesan.
            </p>
        </div>

        <div class="lg:col-span-8">
            @foreach ($faqs as $faq)
                <details class="group border-b-2 border-pensil first:border-t-2" @if ($loop->first) open @endif>
                    <summary class="flex min-h-16 items-start gap-4 py-4">
                        <span aria-hidden="true" class="w-6 shrink-0 font-tangan text-[1.4rem] leading-[1.3] text-margin">T.</span>
                        <span class="flex-1 font-display text-[1.25rem] leading-snug font-bold [font-stretch:92%]">{{ $faq['question'] }}</span>
                        <svg aria-hidden="true" class="mt-1.5 size-5 shrink-0 text-tinta transition-transform group-open:rotate-45 motion-reduce:transition-none" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round">
                            <path d="M10 3.5c.3 4.3 0 8.7-.2 13M3.5 10.2c4.4-.3 8.7-.1 13 .1" />
                        </svg>
                    </summary>
                    <div class="flex gap-4 pb-5">
                        <span aria-hidden="true" class="w-6 shrink-0 font-tangan text-[1.4rem] leading-[1.3] text-tinta">J.</span>
                        <p class="max-w-[42rem] text-[1.0625rem] leading-[1.75rem]">{{ $faq['answer'] }}</p>
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>
