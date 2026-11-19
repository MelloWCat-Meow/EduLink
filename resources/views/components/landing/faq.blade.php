@php
    $questions = [
        [
            'question' => 'Kapan dan bagaimana saya membayar?',
            'answer' => 'Anda membayar setelah tutor menerima pesanan, langsung di platform EduLink Tutor. Metode pembayaran yang tersedia ditampilkan di halaman pembayaran. Tidak perlu transfer ke rekening tutor.',
        ],
        [
            'question' => 'Bagaimana kalau tutor menolak pesanan saya?',
            'answer' => 'Karena pembayaran baru dilakukan setelah tutor menerima, pesanan yang ditolak tidak memotong uang Anda. Anda bisa memesan jadwal lain dengan tutor yang sama atau memilih tutor lain.',
        ],
        [
            'question' => 'Apa beda belajar online dan tutor datang ke rumah?',
            'answer' => 'Online berlangsung lewat video meeting, jadi pilihan tutor tidak dibatasi jarak. Mode ke rumah berarti tutor datang ke alamat Anda, dan hanya tersedia jika rumah Anda masuk area mengajar tutor tersebut. Mode dipilih setiap kali memesan.',
        ],
        [
            'question' => 'Bagaimana tutor diverifikasi?',
            'answer' => 'Tutor mendaftar dengan data diri, pendidikan, pengalaman, mapel, jenjang, tarif, dan lokasi, lalu mengunggah ijazah, KTP, dan sertifikat. Divisi Akademik EduLink memeriksa identitas, dokumen, dan kompetensinya. Profil baru tampil di pencarian setelah lolos.',
        ],
        [
            'question' => 'Bagaimana data anak saya dijaga?',
            'answer' => 'Data anak dikelola dari akun wali Anda, dan Anda yang memilih tutor mana yang memberi les. Semua tutor yang bisa dipesan sudah melewati verifikasi identitas. Rincian data apa yang disimpan dan siapa yang bisa melihatnya ada di Kebijakan Privasi.',
            'link' => true,
        ],
        [
            'question' => 'Bagaimana kalau ada masalah dengan sesi?',
            'answer' => 'Ajukan keluhan lewat akun Anda. Keluhan ditangani oleh tim EduLink, bukan diselesaikan sendiri antara Anda dan tutor.',
        ],
    ];
@endphp

<section id="faq" aria-labelledby="judul-faq" class="mx-auto max-w-3xl px-4 py-12 sm:px-6 md:py-16">
    <h2 id="judul-faq" class="text-2xl font-extrabold tracking-tight sm:text-3xl">Tanya jawab</h2>

    <div class="mt-6 border-t border-garis">
        @foreach ($questions as $item)
            <details class="group border-b border-garis">
                <summary class="flex min-h-14 items-center justify-between gap-4 py-4 text-left font-bold hover:text-merah">
                    <span>{{ $item['question'] }}</span>
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 transition-transform group-open:rotate-45 motion-reduce:transition-none">
                        <path d="M8 2v12M2 8h12" />
                    </svg>
                </summary>
                <div class="pb-5 leading-relaxed text-abu">
                    <p>{{ $item['answer'] }}</p>
                    @isset($item['link'])
                        <p class="mt-2"><a href="{{ route('privacy') }}" class="font-semibold text-dongker underline underline-offset-4 hover:text-merah">Baca Kebijakan Privasi</a></p>
                    @endisset
                </div>
            </details>
        @endforeach
    </div>
</section>
