@props(['subjects', 'levels', 'locations'])

<section aria-labelledby="judul-utama" class="halaman relative pt-6 pb-16 lg:pt-16 lg:pb-24">
    <div class="relative mx-auto max-w-[78rem]">
        <p class="selotip absolute top-6 right-0 hidden w-60 rotate-[4deg] bg-sticky px-5 pt-6 pb-5 font-tangan text-[1.4rem] leading-[1.3] text-pensil shadow-[0_14px_22px_-16px_rgb(0_0_0/0.5)] min-[1360px]:block">
            Les online, atau tutornya datang ke rumah. Anda yang pilih per sesi.
        </p>

        <h1 id="judul-utama" class="max-w-[16ch] font-display text-[clamp(2.4rem,8.2vw,6.6rem)] leading-[0.98] font-extrabold tracking-[-0.025em] [font-stretch:88%] lg:max-w-[17ch]">
            PR Matematika bikin rumah ribut? Panggil <span class="stabilo stabilo-sapu">guru les privat</span> dari
            <span class="relative inline-block whitespace-nowrap">
                Jambi
                <svg aria-hidden="true" class="absolute -bottom-[0.12em] left-0 h-[0.28em] w-full overflow-visible text-margin" viewBox="0 0 200 20" preserveAspectRatio="none" fill="none">
                    <path class="coretan" pathLength="1" d="M3 13C40 6 92 4 197 9M14 17c50-5 110-6 168-3" stroke="currentColor" stroke-width="4" stroke-linecap="round" />
                </svg>
            </span>
            sendiri.
        </h1>

        <div class="mt-5 grid gap-6 lg:mt-14 lg:grid-cols-12 lg:gap-8">
            {{-- On phones the checklist moves below the form so the form starts in the first screen. --}}
            <div class="contents lg:col-span-4 lg:block">
                <p class="order-1 max-w-[34rem] text-[1.0625rem] leading-[1.75rem] lg:text-[1.125rem] lg:leading-[2rem]">
                    Pilih mapel dan jenjang anak Anda, lihat tarif per jam dan jadwal kosong tutornya, lalu pesan sesi online atau minta tutor datang ke rumah.
                </p>

                <ul class="order-3 space-y-4 font-tangan text-[1.25rem] leading-[1.35] text-tinta lg:mt-8 lg:text-[1.3rem]">
                    <li class="flex items-start gap-3">
                        <x-landing.tick class="mt-0.5 size-7 shrink-0" />
                        Ijazah, KTP, dan sertifikat tiap tutor sudah kami cek sebelum profilnya tampil.
                    </li>
                    <li class="flex items-start gap-3">
                        <x-landing.tick class="mt-0.5 size-7 shrink-0" />
                        Ulasan cuma bisa ditulis orang tua yang benar-benar memesan.
                    </li>
                </ul>
            </div>

            <div id="cari" class="relative order-2 lg:col-span-8 lg:col-start-5">
                <p aria-hidden="true" class="mb-1 ml-auto flex w-fit rotate-[-4deg] items-start gap-1 pr-2 font-tangan text-[1.3rem] leading-none text-tinta lg:absolute lg:-top-16 lg:right-6 lg:mb-0">
                    <span class="pt-1">isi tiga ini, beres</span>
                    <svg class="h-10 w-10 shrink-0 lg:h-12 lg:w-11" viewBox="0 0 44 48" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 6c18-2 30 10 28 34" />
                        <path d="M26 33l8 9 6-11" />
                    </svg>
                </p>

                <x-landing.search-sheet :subjects="$subjects" :levels="$levels" :locations="$locations" />
            </div>
        </div>
    </div>
</section>
