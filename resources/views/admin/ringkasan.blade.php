@use('App\Support\Format')
@use('App\Enums\StatusPesanan')

@php($r = $ringkasan)

<x-dasbor.layout title="Ringkasan admin">
    <x-dasbor.judul :title="'Selamat datang, Admin '.auth()->user()->nama" :note="'ringkasan '.Format::periode($r['periode'])">
        <x-slot:actions>
            <form method="get" action="{{ route('admin.beranda') }}" class="flex flex-wrap items-end gap-2">
                <label class="block text-[0.875rem] font-bold" for="isian-periode">Periode
                    <input id="isian-periode" type="month" name="periode" value="{{ $r['periode'] }}" class="kontrol mt-0.5 w-44">
                </label>
                <button type="submit" class="tombol-garis">Tampilkan</button>
            </form>
            <a href="{{ route('admin.laporan.csv', ['periode' => $r['periode']]) }}" class="tombol-garis" download>
                <svg aria-hidden="true" class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 3v10m-4-4 4 4 4-4M4 16h12"/></svg>
                Unduh CSV
            </a>
            <a href="{{ route('admin.laporan.cetak', ['periode' => $r['periode']]) }}" target="_blank" rel="noopener" class="tombol-garis">Cetak<span class="sr-only"> (membuka tab baru)</span></a>
        </x-slot:actions>
    </x-dasbor.judul>

    <dl class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            ['Transaksi berhasil', Format::rupiah($r['total_transaksi']), $r['jumlah_transaksi'].' pembayaran', null],
            ['Pendapatan komisi', Format::rupiah($r['pendapatan_komisi']), 'dari sesi yang dikonfirmasi bulan ini', route('admin.komisi.edit')],
            ['Komisi ditahan', Format::rupiah($r['komisi_ditahan']), 'sesi belum dikonfirmasi, saat ini', null],
            ['Pengembalian dana', Format::rupiah($r['total_pengembalian']), $r['jumlah_pengembalian'].' pesanan dibatalkan bulan ini', route('admin.pengembalian.index')],
            ['Tutor menunggu verifikasi', $r['tutor_menunggu_verifikasi'], 'antrean saat ini', route('admin.verifikasi.index')],
            ['Keluhan terbuka', $r['keluhan_terbuka'], 'baru atau diproses', route('admin.keluhan.index')],
        ] as [$label, $nilai, $keterangan, $tautan])
            <div class="bg-white px-5 py-4 shadow-[0_1px_0_rgb(0_0_0/0.05),0_14px_30px_-24px_rgb(37_37_43/0.5)]">
                <dt class="text-[0.9375rem] font-bold text-pensil-muda">{{ $label }}</dt>
                <dd class="angka mt-1 font-display text-[1.9rem] leading-none font-extrabold">{{ $nilai }}</dd>
                <dd class="mt-1.5 text-[0.875rem] text-pensil-muda">
                    @if ($tautan)
                        <a href="{{ $tautan }}" class="tautan font-normal">{{ $keterangan }}</a>
                    @else
                        {{ $keterangan }}
                    @endif
                </dd>
            </div>
        @endforeach
    </dl>

    <div class="mt-8 grid grid-cols-[minmax(0,1fr)] gap-8 xl:grid-cols-[minmax(0,1.7fr)_minmax(0,1fr)] xl:items-start">
        <x-dasbor.panel heading="Pesanan per hari">
            <x-slot:aside><span class="text-[0.9375rem] text-pensil-muda">{{ $r['jumlah_pesanan'] }} pesanan dibuat</span></x-slot:aside>
            @if ($r['jumlah_pesanan'] === 0)
                <x-dasbor.kosong :message="'Tidak ada pesanan pada '.Format::periode($r['periode']).'.'" />
            @else
                <x-admin.grafik-harian :data="$r['pesanan_per_hari']" :periode="$r['periode']" />
            @endif
        </x-dasbor.panel>

        <div class="space-y-8">
            <x-dasbor.panel heading="Pesanan per status">
                <ul class="divide-y divide-pensil/12">
                    @foreach (StatusPesanan::cases() as $status)
                        <li class="flex items-center justify-between gap-3 py-2">
                            <a href="{{ route('admin.pesanan.index', ['status' => $status->value, 'periode' => $r['periode']]) }}" class="hover:underline"><x-dasbor.status :status="$status" /></a>
                            <span class="angka font-bold">{{ $r['jumlah_per_status'][$status->value] }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-dasbor.panel>

            <x-dasbor.panel heading="Tutor dengan pesanan terbanyak">
                @if ($r['tutor_terlaris']->isEmpty())
                    <x-dasbor.kosong message="Belum ada pesanan yang berjalan atau selesai." />
                @else
                    <ol class="divide-y divide-pensil/12">
                        @foreach ($r['tutor_terlaris'] as $tutor)
                            <li class="flex items-center justify-between gap-3 py-2">
                                <a href="{{ route('admin.pesanan.index', ['tutor' => $tutor->id_tutor, 'periode' => $r['periode']]) }}" class="tautan">{{ $tutor->nama }}</a>
                                <span class="angka font-bold">{{ $tutor->jumlah_pesanan }} pesanan</span>
                            </li>
                        @endforeach
                    </ol>
                    <p class="mt-2 text-[0.8125rem] text-pensil-muda">Tidak menghitung pesanan yang ditolak atau batal.</p>
                @endif
            </x-dasbor.panel>
        </div>
    </div>
</x-dasbor.layout>
