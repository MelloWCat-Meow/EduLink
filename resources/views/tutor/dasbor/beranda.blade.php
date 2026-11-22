@use('App\Support\Format')

<x-dasbor.layout title="Beranda tutor">
    <x-dasbor.judul :title="'Selamat datang, '.$tutor->nama" :note="$tutor->isInternal() ? 'pengajar bimbel EduLink · '.$tutor->id_tutor : 'tutor mitra · '.$tutor->id_tutor">
        <x-slot:actions>
            @if ($tampilDiPencarian)
                <a href="{{ route('tutor.show', $tutor) }}" class="tombol-garis">Lihat profil publik saya</a>
            @endif
        </x-slot:actions>
    </x-dasbor.judul>

    @php($adaTugas = $permintaan->isNotEmpty() || $belumDicatat->isNotEmpty() || $sesiDekat->isNotEmpty())

    <div class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)] lg:items-start">
        {{-- The to-do list is the one sticky note on the page, same as the parent home. --}}
        <section aria-labelledby="perlu-dilakukan" class="selotip relative rotate-[-0.4deg] bg-sticky px-5 pt-6 pb-5 shadow-[0_16px_28px_-20px_rgb(0_0_0/0.45)] sm:px-6">
            <h2 id="perlu-dilakukan" class="font-tangan text-[1.7rem] leading-none font-bold text-tinta">Yang perlu Anda lakukan</h2>

            @unless ($adaTugas)
                <p class="mt-4 font-tangan text-[1.3rem] text-pensil-muda">Tidak ada yang perlu dijawab atau dicatat sekarang.</p>
            @endunless

            @if ($permintaan->isNotEmpty())
                <h3 class="mt-5 font-bold">Permintaan pesanan ({{ $permintaan->count() }})</h3>
                <ul class="mt-2 space-y-3">
                    @foreach ($permintaan as $pesanan)
                        <li class="bg-white/70 px-3 py-3">
                            <a href="{{ route('tutor.pesanan.show', $pesanan) }}" class="tautan">{{ $pesanan->mapel->label() }} untuk {{ $pesanan->siswa->nama }}</a>
                            <p class="text-[0.9375rem]">{{ $pesanan->jadwal->label() }} · {{ $pesanan->jumlah_pertemuan }} pertemuan · <span class="angka">{{ Format::rupiah($pesanan->total_biaya) }}</span></p>
                            <x-dasbor.hitung-mundur :batas="$pesanan->ditahan_hingga" class="mt-1" />
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($belumDicatat->isNotEmpty())
                <h3 class="mt-5 font-bold">Sesi sudah lewat, belum dicatat ({{ $belumDicatat->count() }})</h3>
                <ul class="mt-2 space-y-2">
                    @foreach ($belumDicatat as $sesi)
                        <li class="flex flex-wrap items-center justify-between gap-2 bg-white/70 px-3 py-2">
                            <span>{{ Format::waktu($sesi->waktu, true) }} · {{ $sesi->pesanan->mapel->label() }} · {{ $sesi->pesanan->siswa->nama }}</span>
                            <a href="{{ route('tutor.sesi.index') }}#sesi-{{ $sesi->id_sesi }}" class="tombol-garis min-h-10">Catat<span class="sr-only"> sesi {{ $sesi->id_sesi }}</span></a>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($sesiDekat->isNotEmpty())
                <h3 class="mt-5 font-bold">Sesi hari ini dan besok</h3>
                <ul class="mt-2 space-y-2">
                    @foreach ($sesiDekat as $sesi)
                        <li class="flex flex-wrap items-center justify-between gap-2 bg-white/70 px-3 py-2">
                            <span><span class="font-bold">{{ $sesi->waktu->isToday() ? 'Hari ini' : 'Besok' }}, {{ Format::jam($sesi->waktu) }}</span> · {{ $sesi->pesanan->mapel->label() }} · {{ $sesi->pesanan->siswa->nama }}</span>
                            <x-dasbor.tautan-meeting :sesi="$sesi" :pesanan="$sesi->pesanan" untuk-tutor />
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="space-y-6">
            <x-dasbor.panel heading="Saldo">
                <p class="angka font-display text-[2rem] font-extrabold">{{ Format::rupiah($saldo['saldo']) }}</p>
                <p class="text-[0.9375rem] text-pensil-muda">tersedia untuk dicairkan</p>
                @if (bccomp($saldo['ditahan'], '0', 2) === 1)
                    <p class="mt-2 text-[0.9375rem]"><span class="angka font-bold">{{ Format::rupiah($saldo['ditahan']) }}</span> ditahan sampai sesinya dikonfirmasi orang tua.</p>
                @endif
                <a href="{{ route('tutor.pendapatan.index') }}" class="tautan mt-3 inline-block">Rincian dan pencairan</a>
            </x-dasbor.panel>

            <x-dasbor.panel heading="Rating">
                @if ($rating->jumlah_ulasan > 0)
                    <x-tutor.rating :rata="$rating->rata_rating" :jumlah="$rating->jumlah_ulasan" />
                    <a href="{{ route('tutor.ulasan.index') }}" class="tautan mt-3 inline-block">Baca ulasan</a>
                @else
                    <x-dasbor.kosong message="Belum ada ulasan yang tampil." />
                @endif
            </x-dasbor.panel>

            @if ($menungguBayar > 0)
                <x-dasbor.panel heading="Menunggu pembayaran">
                    <p>{{ $menungguBayar }} pesanan sudah Anda terima dan menunggu dibayar orang tua.</p>
                    <a href="{{ route('tutor.pesanan.index', ['status' => 'menunggu_pembayaran']) }}" class="tautan mt-2 inline-block">Lihat pesanan</a>
                </x-dasbor.panel>
            @endif
        </div>
    </div>
</x-dasbor.layout>
