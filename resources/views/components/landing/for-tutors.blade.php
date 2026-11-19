@php
    $benefits = [
        'Tentukan sendiri tarif per jam, mata pelajaran, jenjang, dan area mengajar.',
        'Atur jadwal ketersediaan, lalu terima atau tolak pesanan yang masuk.',
        'Pesanan dan pembayaran tercatat di platform. Pendapatan bisa Anda cairkan dari akun tutor.',
        'Rating dan ulasan dari murid yang benar-benar Anda ajar membantu orang tua lain memilih Anda.',
    ];

    $documents = [
        'Ijazah terakhir',
        'KTP',
        'Sertifikat pendukung',
    ];

    $verificationSteps = [
        'Isi data diri, pendidikan, pengalaman mengajar, mapel, jenjang, tarif, dan lokasi.',
        'Unggah dokumen yang diminta.',
        'Divisi Akademik EduLink memeriksa identitas, dokumen, dan kompetensi Anda.',
        'Setelah lolos, profil Anda tampil di pencarian dan bisa menerima pesanan.',
    ];
@endphp

<section id="untuk-tutor" aria-labelledby="judul-untuk-tutor" class="on-dongker bg-dongker text-white">
    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 md:py-16">
        <div class="max-w-2xl">
            <h2 id="judul-untuk-tutor" class="text-2xl font-extrabold tracking-tight sm:text-3xl">Mengajar les privat? Daftar jadi tutor EduLink.</h2>
            <p class="mt-3 leading-relaxed text-white/85">Orang tua di Kota Jambi mencari tutor di sini. Lengkapi profil Anda sekali, lalu pesanan datang lewat platform.</p>
        </div>

        <div class="mt-10 grid gap-10 lg:grid-cols-3 lg:gap-12">
            <div class="lg:col-span-1">
                <h3 class="text-lg font-bold">Yang Anda dapat</h3>
                <ul class="mt-4 space-y-3 leading-relaxed text-white/85">
                    @foreach ($benefits as $benefit)
                        <li class="flex gap-3"><span aria-hidden="true" class="mt-2.5 h-0.5 w-3 shrink-0 bg-white"></span>{{ $benefit }}</li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-bold">Dokumen yang disiapkan</h3>
                <ul class="mt-4 space-y-2">
                    @foreach ($documents as $document)
                        <li class="rounded-md border border-white/25 px-4 py-3 font-medium">{{ $document }}</li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-bold">Alur verifikasi</h3>
                <ol class="mt-4 space-y-4">
                    @foreach ($verificationSteps as $step)
                        <li class="flex gap-3 leading-relaxed text-white/85">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-white text-sm font-extrabold text-dongker">{{ $loop->iteration }}</span>
                            <span>{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        <a href="{{ route('tutor.register') }}" class="mt-10 inline-flex min-h-12 items-center justify-center rounded-md bg-white px-6 text-base font-bold text-dongker hover:bg-kertas active:translate-y-px max-sm:w-full">
            Daftar jadi tutor
        </a>
    </div>
</section>
