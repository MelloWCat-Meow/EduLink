@php
    $reasons = [
        [
            'title' => 'Tarif terlihat sejak awal',
            'body' => 'Tarif per jam tercantum di setiap profil. Anda bisa membandingkan beberapa tutor sebelum memutuskan.',
        ],
        [
            'title' => 'Bayar lewat platform',
            'body' => 'Pembayaran dilakukan di EduLink Tutor setelah tutor menerima pesanan, bukan ditransfer langsung ke rekening pribadi.',
        ],
        [
            'title' => 'Ulasan dari pemesan sungguhan',
            'body' => 'Hanya orang tua atau siswa yang sudah menyelesaikan sesi dengan tutor itu yang bisa memberi rating dan ulasan.',
        ],
        [
            'title' => 'Online atau ke rumah',
            'body' => 'Pilih mode di setiap pesanan, sesuai jadwal dan kebutuhan anak.',
        ],
        [
            'title' => 'Satu akun untuk beberapa anak',
            'body' => 'Kelola pesanan kakak dan adik dari satu akun wali, lengkap dengan riwayat pesanan dan jalur keluhan.',
        ],
    ];
@endphp

<section id="alasan" aria-labelledby="judul-alasan" class="mx-auto max-w-6xl px-4 py-12 sm:px-6 md:py-16">
    <h2 id="judul-alasan" class="text-2xl font-extrabold tracking-tight sm:text-3xl">Kenapa memesan lewat EduLink Tutor</h2>

    <div class="mt-8 grid gap-10 lg:grid-cols-[5fr_7fr] lg:gap-14">
        <div class="on-dongker rounded-lg bg-dongker p-6 text-white sm:p-8">
            <h3 class="text-2xl font-extrabold leading-tight">Setiap tutor diperiksa dulu sebelum tampil</h3>
            <p class="mt-3 leading-relaxed text-white/85">
                Divisi Akademik EduLink memeriksa identitas, dokumen (ijazah, KTP, sertifikat), dan kompetensi setiap pendaftar.
                Profil baru muncul di pencarian setelah lolos verifikasi.
            </p>
            <p class="mt-5 border-t border-white/20 pt-5 leading-relaxed text-white/85">
                EduLink Tutor dijalankan oleh PT EduLink Teknologi Indonesia, bimbingan belajar yang sudah mengajar siswa SD, SMP, dan SMA di Kota Jambi.
                Kalau ada masalah, ada tim yang bisa Anda hubungi.
            </p>
        </div>

        <ul class="divide-y divide-garis border-y border-garis">
            @foreach ($reasons as $reason)
                <li class="py-5 sm:grid sm:grid-cols-[14rem_1fr] sm:gap-6">
                    <h3 class="font-bold">{{ $reason['title'] }}</h3>
                    <p class="mt-1 leading-relaxed text-abu sm:mt-0">{{ $reason['body'] }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>
