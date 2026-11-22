<?php

/*
|--------------------------------------------------------------------------
| Landing Page Content
|--------------------------------------------------------------------------
|
| Data for the EduLink Tutor landing page. The tutor cards are sample data
| shown as a preview of the real profile card, not real tutors. Contact
| details stay null until the company supplies the real values.
|
*/

return [

    /*
    | Shown in the landing footer. An empty value hides its line. The WhatsApp
    | number is kept as digits in international form (62...) for wa.me, so
    | "0812-..." and "+62 812-..." both work.
    */
    'contact' => [
        'whatsapp' => (function (): ?string {
            $angka = preg_replace('/\D/', '', (string) env('EDULINK_WHATSAPP'));

            return $angka === '' ? null : preg_replace('/^0/', '62', $angka);
        })(),
        'address' => trim((string) env('EDULINK_ADDRESS')) ?: null,
    ],

    /**
     * Keys are the query values sent to /tutor as `jenjang`; `subjects` are
     * the popular subjects linked from the landing page for each level.
     *
     * @var array<string, array{label: string, grades: string, subjects: list<string>}>
     */
    'levels' => [
        'sd' => [
            'label' => 'SD',
            'grades' => 'Kelas 1 sampai 6',
            'subjects' => ['Calistung', 'Matematika', 'IPA', 'IPS', 'Bahasa Indonesia', 'Bahasa Inggris'],
        ],
        'smp' => [
            'label' => 'SMP',
            'grades' => 'Kelas 7 sampai 9',
            'subjects' => ['Matematika', 'IPA', 'Bahasa Inggris', 'IPS', 'Bahasa Indonesia'],
        ],
        'sma' => [
            'label' => 'SMA',
            'grades' => 'Kelas 10 sampai 12',
            'subjects' => ['Matematika', 'Fisika', 'Kimia', 'Biologi', 'Bahasa Inggris', 'Persiapan UTBK'],
        ],
    ],

    /** @var list<string> */
    'subjects' => [
        'Calistung',
        'Matematika',
        'IPA',
        'IPS',
        'Fisika',
        'Kimia',
        'Biologi',
        'Bahasa Indonesia',
        'Bahasa Inggris',
        'Persiapan UTBK',
    ],

    /**
     * Kecamatan in Kota Jambi.
     *
     * @var list<string>
     */
    'locations' => [
        'Alam Barajo',
        'Danau Sipin',
        'Danau Teluk',
        'Jambi Selatan',
        'Jambi Timur',
        'Jelutung',
        'Kota Baru',
        'Paal Merah',
        'Pasar Jambi',
        'Pelayangan',
        'Telanaipura',
    ],

    /**
     * The booking flow from a parent's point of view, in order.
     *
     * @var list<array{title: string, body: string}>
     */
    'steps' => [
        [
            'title' => 'Cari dan bandingkan',
            'body' => 'Saring tutor menurut mapel, jenjang, kecamatan, tarif, dan rating. Tiap profil memuat tarif per jam dan jadwal yang masih kosong.',
        ],
        [
            'title' => 'Pesan jadwalnya',
            'body' => 'Pilih jam yang cocok, lalu tentukan cara belajarnya: sesi online, atau tutor datang ke rumah Anda.',
        ],
        [
            'title' => 'Tutor menerima, Anda membayar',
            'body' => 'Pembayaran baru diminta setelah tutor menerima pesanan, dan dilakukan di EduLink. Kalau tutor menolak, Anda bisa memilih tutor lain.',
        ],
        [
            'title' => 'Belajar, lalu beri ulasan',
            'body' => 'Selesai sesi, Anda menilai tutornya. Ulasan hanya bisa ditulis oleh orang tua yang benar-benar memesan.',
        ],
    ],

    /**
     * Policy answers below describe the intended product behaviour and must
     * be confirmed by PT EduLink before launch.
     *
     * @var list<array{question: string, answer: string}>
     */
    'faqs' => [
        [
            'question' => 'Bagaimana cara membayarnya?',
            'answer' => 'Anda membayar di EduLink setelah tutor menerima pesanan, sebelum sesi dimulai. Tutor tidak menagih langsung ke Anda, jadi tidak ada transfer ke rekening pribadi.',
        ],
        [
            'question' => 'Bagaimana kalau tutor menolak pesanan saya?',
            'answer' => 'Pesanan batal dan Anda belum membayar apa pun, karena pembayaran baru diminta setelah tutor menerima. Anda bisa langsung memesan tutor lain dari hasil pencarian yang sama.',
        ],
        [
            'question' => 'Apa yang dicek sebelum tutor tampil di pencarian?',
            'answer' => 'Tim EduLink memeriksa KTP, ijazah terakhir, dan sertifikat yang diunggah tutor. Profil baru muncul di pencarian setelah semua dokumen itu disetujui.',
        ],
        [
            'question' => 'Data anak saya dipakai untuk apa?',
            'answer' => 'Hanya untuk mengatur sesi: nama panggilan, jenjang, dan mapel anak. Alamat rumah dibagikan ke tutor hanya untuk sesi di rumah, dan hanya setelah tutor menerima pesanan. Rinciannya ada di kebijakan privasi.',
        ],
        [
            'question' => 'Boleh ganti dari online ke tutor datang ke rumah?',
            'answer' => 'Boleh. Cara belajar dipilih setiap kali memesan sesi, selama tutornya melayani cara itu. Keterangannya ada di profil tiap tutor.',
        ],
    ],

    /**
     * Sample cards for the profile preview. Not real tutors.
     *
     * @var list<array{name: string, initials: string, subjects: list<string>, levels: list<string>, hourly_rate: int, rating: float, review_count: int, area: string, modes: list<string>}>
     */
    'sample_tutors' => [
        [
            'name' => 'Rina Marlina',
            'initials' => 'RM',
            'subjects' => ['Matematika', 'IPA'],
            'levels' => ['sd', 'smp'],
            'hourly_rate' => 75000,
            'rating' => 4.8,
            'review_count' => 23,
            'area' => 'Telanaipura, Kota Baru',
            'modes' => ['Online', 'Ke rumah'],
        ],
        [
            'name' => 'Fadli Ramadhan',
            'initials' => 'FR',
            'subjects' => ['Fisika', 'Persiapan UTBK'],
            'levels' => ['sma'],
            'hourly_rate' => 110000,
            'rating' => 4.9,
            'review_count' => 17,
            'area' => 'Alam Barajo',
            'modes' => ['Online', 'Ke rumah'],
        ],
        [
            'name' => 'Sari Wulandari',
            'initials' => 'SW',
            'subjects' => ['Calistung', 'Bahasa Indonesia'],
            'levels' => ['sd'],
            'hourly_rate' => 60000,
            'rating' => 4.7,
            'review_count' => 31,
            'area' => 'Jelutung, Pasar Jambi',
            'modes' => ['Ke rumah'],
        ],
        [
            'name' => 'Andi Pratama',
            'initials' => 'AP',
            'subjects' => ['Bahasa Inggris'],
            'levels' => ['smp', 'sma'],
            'hourly_rate' => 90000,
            'rating' => 4.6,
            'review_count' => 12,
            'area' => 'Seluruh Kota Jambi',
            'modes' => ['Online'],
        ],
    ],

];
