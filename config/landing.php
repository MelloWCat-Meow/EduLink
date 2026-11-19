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

    'contact' => [
        'whatsapp' => env('EDULINK_WHATSAPP'),
        'address' => env('EDULINK_ADDRESS'),
    ],

    /**
     * Keys are the query values sent to /tutor; colors follow the school
     * uniform of each level (SD merah, SMP dongker, SMA abu).
     *
     * @var array<string, array{label: string, grades: string, subjects: list<string>}>
     */
    'levels' => [
        'sd' => [
            'label' => 'SD',
            'grades' => 'Kelas 1 sampai 6',
            'subjects' => ['Calistung', 'Matematika', 'IPAS', 'Bahasa Indonesia', 'Bahasa Inggris'],
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
        'IPAS',
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
