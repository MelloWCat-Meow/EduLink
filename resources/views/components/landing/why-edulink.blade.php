@php
    $comparisons = [
        [
            'before' => 'Tanya tarif satu per satu lewat chat',
            'after' => 'Tarif per jam tertulis di profil sebelum Anda memesan.',
        ],
        [
            'before' => 'Transfer ke rekening pribadi, lalu berharap gurunya datang',
            'after' => 'Bayar di EduLink, dan baru setelah tutor menerima pesanan.',
        ],
        [
            'before' => 'Percaya testimoni yang tidak jelas asalnya',
            'after' => 'Ulasan hanya dari orang tua yang benar-benar memesan sesi.',
        ],
        [
            'before' => 'Mencocokkan jadwal lewat telepon bolak-balik',
            'after' => 'Jadwal kosong tutor terlihat di profilnya.',
        ],
        [
            'before' => 'Bingung harus mengadu ke siapa kalau ada masalah',
            'after' => 'Dikelola bimbel EduLink di Kota Jambi, dengan admin yang bisa Anda hubungi.',
        ],
    ];
@endphp

<section aria-labelledby="judul-alasan" class="kertas-garis sobek-atas halaman relative -mt-4 pt-20 pb-20 lg:pt-28 lg:pb-28">
    <div class="mx-auto grid max-w-[78rem] gap-10 lg:grid-cols-12 lg:gap-8">
        <div class="lg:col-span-5">
            <h2 id="judul-alasan" class="max-w-[15ch] font-display text-[clamp(2rem,4.4vw,3.4rem)] leading-[1.02] font-extrabold tracking-tight [font-stretch:88%]">
                Kenapa tidak cari sendiri di grup WhatsApp?
            </h2>
            <p class="mt-4 max-w-[30rem] text-[1.0625rem] leading-[1.75rem]">
                Bisa saja. Tapi ini yang biasanya terjadi, dan yang berubah kalau Anda memesan lewat EduLink.
            </p>
        </div>

        <ul class="lg:col-span-7 lg:col-start-6 lg:pt-3">
            @foreach ($comparisons as $comparison)
                <li class="border-b border-dashed border-pensil-muda py-5 first:pt-0 last:border-0">
                    <p class="text-[1.0625rem] leading-[1.6] text-pensil-muda">
                        <span class="sr-only">Biasanya: </span><span class="coret-lama">{{ $comparison['before'] }}</span>
                    </p>
                    <p class="mt-1 flex items-start gap-2 font-tangan text-[1.4rem] leading-[1.3] text-tinta">
                        <x-landing.tick class="mt-1 size-6 shrink-0" />
                        <span><span class="sr-only">Di EduLink: </span>{{ $comparison['after'] }}</span>
                    </p>
                </li>
            @endforeach
        </ul>
    </div>
</section>
