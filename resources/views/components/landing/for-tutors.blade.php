@php
    $benefits = [
        'Murid datang dari orang tua di Kota Jambi, tanpa Anda promosi sendiri.',
        'Anda yang mengatur jadwal kosong, dan bebas menerima atau menolak pesanan.',
        'Pembayaran diurus EduLink, jadi tidak perlu menagih orang tua.',
        'Mengajar online, datang ke rumah murid, atau keduanya.',
    ];

    $documents = [
        ['name' => 'KTP', 'note' => 'yang masih berlaku'],
        ['name' => 'Ijazah terakhir', 'note' => 'hasil pindai atau foto yang jelas'],
        ['name' => 'Sertifikat', 'note' => 'pelatihan, lomba, atau keahlian; boleh lebih dari satu, boleh juga tidak ada'],
    ];

    $flow = [
        'Buat akun tutor dan isi profil: mapel, jenjang, wilayah, dan tarif.',
        'Unggah KTP, ijazah, dan sertifikat.',
        'Tim EduLink memeriksa berkas Anda.',
        'Setelah disetujui, profil Anda tampil di pencarian orang tua.',
    ];
@endphp

<section id="jadi-tutor" aria-labelledby="judul-jadi-tutor" class="kertas-garis sobek-atas halaman relative -mt-4 pt-20 pb-20 lg:pt-28 lg:pb-28">
    <div class="mx-auto max-w-[78rem]">
        <p class="font-tangan text-[1.35rem] text-tinta">untuk Anda yang mengajar di Kota Jambi</p>
        <h2 id="judul-jadi-tutor" class="mt-1 max-w-[18ch] font-display text-[clamp(2rem,5vw,3.6rem)] leading-[1.02] font-extrabold tracking-tight [font-stretch:88%]">
            Mengajar les privat lewat EduLink
        </h2>

        <div class="mt-10 grid gap-12 lg:grid-cols-12 lg:gap-8">
            <div class="selotip relative rotate-[-1.2deg] self-start bg-sticky px-6 pt-8 pb-7 shadow-[0_16px_26px_-20px_rgb(0_0_0/0.55)] lg:col-span-5">
                <h3 class="font-display text-[1.5rem] font-extrabold [font-stretch:88%]">Yang Anda dapat</h3>
                <ul class="mt-3 space-y-3 text-[1.0625rem] leading-[1.55]">
                    @foreach ($benefits as $benefit)
                        <li class="flex items-start gap-3">
                            <x-landing.tick class="mt-0.5 size-6 shrink-0 text-tinta" />
                            {{ $benefit }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="lg:col-span-6 lg:col-start-7">
                <h3 class="font-display text-[1.5rem] font-extrabold [font-stretch:88%]">Siapkan dokumen ini</h3>
                <ul class="mt-3">
                    @foreach ($documents as $document)
                        <li class="flex items-start gap-3 border-b border-dashed border-pensil-muda py-3">
                            <span aria-hidden="true" class="mt-0.5 size-6 shrink-0 border-2 border-pensil"></span>
                            <span class="leading-[1.55]"><span class="font-bold">{{ $document['name'] }}</span>, {{ $document['note'] }}</span>
                        </li>
                    @endforeach
                </ul>

                <h3 class="mt-10 font-display text-[1.5rem] font-extrabold [font-stretch:88%]">Alur verifikasinya</h3>
                <ol class="mt-4 space-y-4">
                    @foreach ($flow as $step)
                        <li class="grid grid-cols-[2.5rem_1fr] items-start gap-3">
                            <span aria-hidden="true" class="grid size-10 place-items-center rounded-full border-2 border-tinta font-tangan text-[1.35rem] leading-none font-bold text-tinta">{{ $loop->iteration }}</span>
                            <p class="pt-1.5 leading-[1.55]">{{ $step }}</p>
                        </li>
                    @endforeach
                </ol>

                <a href="{{ route('tutor.register') }}" class="mt-9 inline-flex min-h-13 items-center rounded-[0.7rem_1.1rem_0.6rem_1.2rem] bg-tinta px-7 font-display text-[1.2rem] font-bold text-white shadow-[4px_4px_0_var(--color-stabilo)] transition-[translate,box-shadow] hover:-translate-y-0.5 hover:shadow-[6px_6px_0_var(--color-stabilo)] motion-reduce:transition-none">
                    Daftar jadi tutor
                </a>
            </div>
        </div>
    </div>
</section>
