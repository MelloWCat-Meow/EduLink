@use('App\Support\Format')

<x-dasbor.layout title="Beranda">
    <x-dasbor.judul :title="'Selamat datang, '.$akun->nama" note="ringkasan les anak-anak Anda" />

    @php($adaTugas = $menungguBayar->isNotEmpty() || $sesiTerdekat || $belumDiulas->isNotEmpty() || $jumlahAnak === 0)

    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)] lg:items-start">
        {{-- The to-do list is the one sticky note on the page. --}}
        <section aria-labelledby="perlu-dilakukan" class="selotip relative rotate-[-0.5deg] bg-sticky px-5 pt-6 pb-5 shadow-[0_16px_28px_-20px_rgb(0_0_0/0.45)] sm:px-6">
            <h2 id="perlu-dilakukan" class="font-tangan text-[1.6rem] leading-tight font-bold text-tinta">Yang perlu Anda lakukan</h2>

            @if (! $adaTugas)
                <p class="mt-3 font-tangan text-[1.25rem] text-pensil-muda">Tidak ada yang perlu dikerjakan sekarang. Jadwal sesi berikutnya akan muncul di sini.</p>
            @else
                <ul class="mt-3 divide-y divide-dashed divide-pensil/25">
                    @if ($jumlahAnak === 0)
                        <li class="py-3">
                            <p class="font-bold">Tambahkan data anak</p>
                            <p class="text-[0.9375rem]">Pesanan selalu dibuat untuk seorang anak, jadi datanya perlu ada lebih dulu.</p>
                            <a href="{{ route('anak.index') }}" class="tautan mt-1 inline-block">Tambah anak</a>
                        </li>
                    @endif

                    @foreach ($menungguBayar as $pesanan)
                        <li class="py-3">
                            <p class="font-bold">Bayar {{ $pesanan->mapel->label() }} untuk {{ $pesanan->siswa->nama }}</p>
                            <p class="text-[0.9375rem]">
                                {{ $pesanan->tutor->nama }} sudah menerima. Total <span class="angka font-bold">{{ Format::rupiah($pesanan->total_biaya) }}</span>.
                                Batas bayar {{ Format::sisaWaktu($pesanan->ditahan_hingga) }} ({{ Format::waktu($pesanan->ditahan_hingga) }}).
                            </p>
                            <a href="{{ route('pesanan.show', $pesanan) }}#bayar" class="tombol mt-2 min-h-10 text-[0.9375rem]">Bayar sekarang</a>
                        </li>
                    @endforeach

                    @if ($sesiTerdekat)
                        @php($p = $sesiTerdekat->pesanan)
                        <li class="py-3">
                            <p class="font-bold">Sesi terdekat: {{ Format::waktu($sesiTerdekat->waktu, true) }}</p>
                            <p class="text-[0.9375rem]">{{ $p->mapel->label() }} untuk {{ $p->siswa->nama }} dengan {{ $p->tutor->nama }}.</p>
                            @if ($sesiTerdekat->tautan_meeting)
                                <a href="{{ $sesiTerdekat->tautan_meeting }}" class="tautan mt-1 inline-block" rel="noopener" target="_blank">Buka ruang meeting</a>
                            @else
                                <p class="text-[0.9375rem]">Tutor datang ke rumah.</p>
                            @endif
                        </li>
                    @endif

                    @foreach ($belumDiulas as $pesanan)
                        <li class="py-3">
                            <p class="font-bold">Beri ulasan untuk {{ $pesanan->tutor->nama }}</p>
                            <p class="text-[0.9375rem]">{{ $pesanan->mapel->label() }} untuk {{ $pesanan->siswa->nama }} sudah selesai.</p>
                            <a href="{{ route('pesanan.show', $pesanan) }}#ulasan" class="tautan mt-1 inline-block">Tulis ulasan</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <x-dasbor.panel heading="Pesanan yang sedang berjalan">
            <x-slot:aside><a href="{{ route('pesanan.index') }}" class="tautan text-[0.9375rem]">Semua pesanan</a></x-slot:aside>

            @if ($aktif->isEmpty())
                <x-dasbor.kosong message="Belum ada pesanan yang berjalan.">
                    <a href="{{ route('tutor.index') }}" class="tombol">Cari tutor</a>
                </x-dasbor.kosong>
            @else
                <ul>
                    @foreach ($aktif as $pesanan)
                        <x-dasbor.baris-pesanan :pesanan="$pesanan" :href="route('pesanan.show', $pesanan)" />
                    @endforeach
                </ul>
            @endif
        </x-dasbor.panel>
    </div>
</x-dasbor.layout>
