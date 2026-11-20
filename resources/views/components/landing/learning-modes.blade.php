@php
    $rows = [
        [
            'aspect' => 'Tempat belajar',
            'online' => 'Di rumah Anda, lewat panggilan video.',
            'home' => 'Di rumah Anda, tutor yang datang.',
        ],
        [
            'aspect' => 'Cocok untuk',
            'online' => 'Siswa SMP dan SMA yang sudah terbiasa belajar di layar, atau jadwal yang padat.',
            'home' => 'Anak SD, calistung, dan anak yang lebih fokus kalau gurunya duduk di sebelahnya.',
        ],
        [
            'aspect' => 'Yang perlu disiapkan',
            'online' => 'Laptop atau HP dengan internet yang stabil.',
            'home' => 'Meja belajar dan alamat yang jelas. Alamat hanya dibagikan ke tutor yang menerima pesanan.',
        ],
        [
            'aspect' => 'Jangkauan',
            'online' => 'Tutor dari kecamatan mana pun di Kota Jambi.',
            'home' => 'Tutor yang wilayahnya mencakup kecamatan Anda.',
        ],
    ];
@endphp

<section aria-labelledby="judul-cara-belajar" class="kertas-kotak sobek-atas halaman relative -mt-4 pt-20 pb-20 lg:pt-28 lg:pb-28">
    <div class="mx-auto max-w-[78rem]">
        <h2 id="judul-cara-belajar" class="max-w-[20ch] font-display text-[clamp(2rem,5vw,3.6rem)] leading-[1.02] font-extrabold tracking-tight [font-stretch:88%]">
            Online atau tutor datang ke rumah?
        </h2>
        <p class="mt-4 max-w-[38rem] text-[1.0625rem] leading-[1.75rem]">
            Anda memilihnya setiap kali memesan sesi. Tarif per jam untuk masing-masing cara tertulis di profil tutor.
        </p>

        <div class="mt-10 bg-white p-4 shadow-[0_1px_0_rgb(0_0_0/0.04),0_20px_36px_-30px_rgb(37_37_43/0.6)] sm:p-8">
            <table class="w-full border-collapse text-left">
                <caption class="sr-only">Perbandingan belajar online dan tutor datang ke rumah</caption>
                <thead class="hidden md:table-header-group">
                    <tr class="border-b-2 border-pensil">
                        <td class="w-[22%] pb-3"></td>
                        <th scope="col" class="pb-3 font-display text-[1.6rem] font-extrabold [font-stretch:88%]"><span class="stabilo">Online</span></th>
                        <th scope="col" class="pb-3 pl-6 font-display text-[1.6rem] font-extrabold [font-stretch:88%]"><span class="stabilo">Ke rumah</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr class="block border-b border-dashed border-pensil-muda py-4 last:border-0 md:table-row md:py-0">
                            <th scope="row" class="block pb-2 font-tangan text-[1.3rem] leading-snug font-normal text-tinta md:table-cell md:py-5 md:pr-6 md:align-top">{{ $row['aspect'] }}</th>
                            <td class="block py-1 leading-[1.65] md:table-cell md:py-5 md:align-top">
                                <span class="font-bold md:hidden">Online: </span>{{ $row['online'] }}
                            </td>
                            <td class="block py-1 leading-[1.65] md:table-cell md:border-l md:border-dashed md:border-pensil-muda md:py-5 md:pl-6 md:align-top">
                                <span class="font-bold md:hidden">Ke rumah: </span>{{ $row['home'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
