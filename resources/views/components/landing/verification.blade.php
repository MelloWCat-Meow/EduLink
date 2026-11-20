@php
    $checks = [
        ['document' => 'KTP', 'checked' => 'Identitas asli dan sama dengan data pendaftar'],
        ['document' => 'Ijazah terakhir', 'checked' => 'Dokumen asli dan sesuai riwayat pendidikan di profil'],
        ['document' => 'Sertifikat', 'checked' => 'Pelatihan atau keahlian yang disebut di profil, bila ada'],
    ];
@endphp

<section id="verifikasi" aria-labelledby="judul-verifikasi" class="di-gelap sobek-atas halaman relative -mt-4 overflow-x-clip bg-tinta-tua pt-20 pb-20 text-white lg:pt-28 lg:pb-28">
    <div class="mx-auto grid max-w-[78rem] gap-12 lg:grid-cols-12 lg:items-center lg:gap-8">
        <div class="lg:order-2 lg:col-span-4 lg:col-start-9">
            <h2 id="judul-verifikasi" class="font-display text-[clamp(2rem,4.4vw,3.2rem)] leading-[1.04] font-extrabold tracking-tight [font-stretch:88%]">
                Berkas tiap tutor kami periksa sebelum profilnya tampil
            </h2>
            <p class="mt-5 max-w-[34rem] text-[1.0625rem] leading-[1.75rem] text-white/90">
                Calon tutor mengunggah KTP, ijazah, dan sertifikat saat mendaftar. Tim EduLink memeriksanya satu per satu. Profil yang belum disetujui tidak muncul di pencarian, jadi setiap tutor yang Anda lihat sudah melewati lembar ini.
            </p>
        </div>

        <div class="relative lg:order-1 lg:col-span-7">
            <figure class="relative rotate-[0.6deg] bg-white px-5 pt-6 pb-32 text-pensil shadow-[0_30px_50px_-30px_rgb(0_0_0/0.8)] sm:px-9 sm:pt-8 lg:pb-14">
                <div class="flex items-start justify-between gap-4 border-b-[3px] border-double border-pensil pb-4">
                    <div>
                        <p class="font-display text-[0.8125rem] font-bold tracking-[0.12em] [font-stretch:90%]">PT EDULINK TEKNOLOGI INDONESIA</p>
                        <p class="text-[0.8125rem] text-pensil-muda">Kota Jambi</p>
                    </div>
                    <p class="shrink-0 border border-pensil-muda px-2 py-1 text-[0.75rem] text-pensil-muda">Contoh berkas</p>
                </div>

                <figcaption class="mt-5 font-display text-[1.35rem] leading-tight font-extrabold [font-stretch:90%] sm:text-[1.6rem]">
                    Lembar pemeriksaan berkas tutor
                </figcaption>

                <dl class="mt-4 grid grid-cols-[7.5rem_1fr] gap-y-2 text-[0.9375rem] sm:grid-cols-[9rem_1fr]">
                    <dt class="text-pensil-muda">Nama tutor</dt>
                    <dd class="border-b border-dotted border-pensil-muda font-tangan text-[1.2rem] leading-tight text-tinta">Rina Marlina</dd>
                    <dt class="text-pensil-muda">Mapel diajukan</dt>
                    <dd class="border-b border-dotted border-pensil-muda font-tangan text-[1.2rem] leading-tight text-tinta">Matematika, IPA (SD dan SMP)</dd>
                </dl>

                <table class="mt-6 w-full border-collapse text-left text-[0.9375rem]">
                    <caption class="sr-only">Dokumen yang diperiksa</caption>
                    <thead>
                        <tr class="border-y border-pensil">
                            <th scope="col" class="py-2 pr-3 font-bold">Dokumen</th>
                            <th scope="col" class="hidden py-2 pr-3 font-bold sm:table-cell">Yang diperiksa</th>
                            <th scope="col" class="w-16 py-2 text-center font-bold">Sesuai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($checks as $check)
                            <tr class="border-b border-pensil/30 align-top">
                                <th scope="row" class="py-3 pr-3 font-bold">
                                    {{ $check['document'] }}
                                    <span class="mt-1 block font-normal leading-snug text-pensil-muda sm:hidden">{{ $check['checked'] }}</span>
                                </th>
                                <td class="hidden py-3 pr-3 leading-snug sm:table-cell">{{ $check['checked'] }}</td>
                                <td class="py-3">
                                    <span class="mx-auto grid size-7 place-items-center border-[1.5px] border-pensil text-tinta">
                                        <x-landing.tick class="size-6" />
                                        <span class="sr-only">Ya</span>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <p class="mt-5 max-w-[26rem] text-[0.9375rem] leading-snug lg:max-w-[17rem]">
                    <span class="font-bold">Hasil:</span> berkas lengkap dan sesuai. Profil boleh tampil di pencarian EduLink Tutor.
                </p>

                {{-- The stamp: the one heavy, official mark on an otherwise hand-drawn page. --}}
                <div aria-hidden="true" class="cap-turun pointer-events-none absolute right-3 bottom-5 sm:right-8 sm:bottom-8">
                    <div class="cap rotate-[-12deg] border-[3px] border-cap px-3 py-2 text-center text-cap sm:px-4">
                        <div class="border border-cap px-3 py-1.5 sm:px-4">
                            <p class="font-display text-[1.45rem] leading-none font-extrabold tracking-[0.06em] [font-stretch:80%] sm:text-[2rem]">TERVERIFIKASI</p>
                            <p class="mt-1 text-[0.6875rem] font-bold tracking-[0.08em] sm:text-[0.75rem]">TIM EDULINK, KOTA JAMBI</p>
                        </div>
                    </div>
                </div>
                <p class="sr-only">Cap: terverifikasi oleh tim EduLink, Kota Jambi.</p>
            </figure>
        </div>
    </div>
</section>
