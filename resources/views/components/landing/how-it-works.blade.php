@php
    $steps = [
        [
            'title' => 'Cari tutor',
            'body' => 'Saring berdasarkan mata pelajaran, jenjang, lokasi, tarif, dan rating. Buka profilnya untuk melihat pengalaman, area mengajar, dan ulasan.',
        ],
        [
            'title' => 'Pilih jadwal dan mode',
            'body' => 'Ambil slot dari jadwal kosong tutor, tentukan durasi, lalu pilih belajar online atau tutor datang ke rumah.',
        ],
        [
            'title' => 'Tutor menerima, lalu Anda bayar',
            'body' => 'Pesanan dikirim ke tutor. Setelah tutor menerima, Anda membayar lewat platform. Kalau tutor menolak, tidak ada yang perlu dibayar.',
        ],
        [
            'title' => 'Belajar',
            'body' => 'Sesi berjalan sesuai jadwal. Tutor mencatat setiap sesi, dan semua pesanan tersimpan di riwayat akun Anda.',
        ],
        [
            'title' => 'Beri ulasan',
            'body' => 'Setelah sesi selesai, beri rating 1 sampai 5 dan ulasan. Hanya pemesan yang sudah belajar dengan tutor itu yang bisa mengulas.',
        ],
    ];
@endphp

<section id="cara-kerja" aria-labelledby="judul-cara-kerja" class="border-y border-garis bg-kertas">
    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 md:py-16">
        <h2 id="judul-cara-kerja" class="text-2xl font-extrabold tracking-tight sm:text-3xl">Dari mencari sampai belajar</h2>
        <p class="mt-2 max-w-2xl text-abu">Semua langkah dilakukan sendiri di platform, tanpa perlu menghubungi admin.</p>

        <ol class="mt-8 grid gap-0 lg:mt-10 lg:grid-cols-5 lg:gap-6">
            @foreach ($steps as $step)
                <li class="relative flex gap-4 pb-8 last:pb-0 lg:block lg:pb-0">
                    {{-- Connector line: vertical on mobile, horizontal on desktop. --}}
                    @unless ($loop->last)
                        <span aria-hidden="true" class="absolute top-10 bottom-0 left-5 w-0.5 bg-garis lg:top-5 lg:right-[-1.5rem] lg:bottom-auto lg:left-10 lg:h-0.5 lg:w-auto"></span>
                    @endunless
                    <span @class([
                        'relative flex size-10 shrink-0 items-center justify-center rounded-full text-base font-extrabold',
                        'bg-dongker text-white' => $loop->iteration !== 3,
                        'bg-merah text-white' => $loop->iteration === 3,
                    ])>{{ $loop->iteration }}</span>
                    <div class="lg:mt-4">
                        <h3 class="text-lg font-bold leading-snug">{{ $step['title'] }}</h3>
                        <p class="mt-1.5 text-[0.95rem] leading-relaxed text-abu">{{ $step['body'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
