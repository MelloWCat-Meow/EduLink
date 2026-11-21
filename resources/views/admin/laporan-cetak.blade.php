@use('App\Support\Format')
@use('App\Enums\StatusPesanan')

@php($r = $ringkasan)

{{-- Print version of the admin summary. Save as PDF from the browser's print dialog. --}}
<x-layouts.app :title="'Laporan '.Format::periode($r['periode'])">
    <main id="konten" class="cetak mx-auto max-w-[60rem] px-6 py-8 text-[0.9375rem] print:max-w-none print:p-0">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 print:hidden">
            <a href="{{ route('admin.beranda', ['periode' => $r['periode']]) }}" class="tautan">Kembali ke ringkasan</a>
            <button type="button" onclick="window.print()" class="tombol">Cetak atau simpan sebagai PDF</button>
        </div>

        <header class="border-b-2 border-pensil pb-3">
            <p class="font-display text-[1.2rem] font-extrabold">EduLink Tutor · PT EduLink Teknologi Indonesia</p>
            <h1 class="font-display text-[1.9rem] leading-tight font-extrabold">Laporan {{ Format::periode($r['periode']) }}</h1>
            <p class="text-pensil-muda">Dicetak {{ Format::waktu(now()) }} oleh {{ auth()->user()->nama }}</p>
        </header>

        <section class="mt-6" aria-labelledby="cetak-angka">
            <h2 id="cetak-angka" class="font-display text-[1.2rem] font-extrabold">Angka utama</h2>
            <table class="tabel mt-2">
                <tbody>
                    <tr><th scope="row">Pesanan dibuat</th><td class="angka text-right">{{ $r['jumlah_pesanan'] }}</td></tr>
                    <tr><th scope="row">Transaksi berhasil ({{ $r['jumlah_transaksi'] }} pembayaran)</th><td class="angka text-right">{{ Format::rupiah($r['total_transaksi']) }}</td></tr>
                    <tr><th scope="row">Pendapatan komisi</th><td class="angka text-right">{{ Format::rupiah($r['pendapatan_komisi']) }}</td></tr>
                    <tr><th scope="row">Pengembalian dana ({{ $r['jumlah_pengembalian'] }} pesanan)</th><td class="angka text-right">{{ Format::rupiah($r['total_pengembalian']) }}</td></tr>
                    <tr><th scope="row">Tutor menunggu verifikasi (saat dicetak)</th><td class="angka text-right">{{ $r['tutor_menunggu_verifikasi'] }}</td></tr>
                    <tr><th scope="row">Keluhan terbuka (saat dicetak)</th><td class="angka text-right">{{ $r['keluhan_terbuka'] }}</td></tr>
                </tbody>
            </table>
        </section>

        <div class="mt-6 grid gap-6 sm:grid-cols-2 print:grid-cols-2">
            <section aria-labelledby="cetak-status">
                <h2 id="cetak-status" class="font-display text-[1.2rem] font-extrabold">Pesanan per status</h2>
                <table class="tabel mt-2">
                    <tbody>
                        @foreach (StatusPesanan::cases() as $status)
                            <tr><th scope="row">{{ $status->label() }}</th><td class="angka text-right">{{ $r['jumlah_per_status'][$status->value] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
            <section aria-labelledby="cetak-tutor">
                <h2 id="cetak-tutor" class="font-display text-[1.2rem] font-extrabold">Tutor dengan pesanan terbanyak</h2>
                <table class="tabel mt-2">
                    <tbody>
                        @forelse ($r['tutor_terlaris'] as $tutor)
                            <tr><th scope="row">{{ $tutor->nama }}</th><td class="angka text-right">{{ $tutor->jumlah_pesanan }} pesanan</td></tr>
                        @empty
                            <tr><td>Belum ada pesanan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </div>

        <section class="mt-6 break-inside-avoid-page" aria-labelledby="cetak-pesanan">
            <h2 id="cetak-pesanan" class="font-display text-[1.2rem] font-extrabold">Daftar pesanan ({{ $daftarPesanan->count() }})</h2>
            @if ($daftarPesanan->isEmpty())
                <p class="mt-2">Tidak ada pesanan pada periode ini.</p>
            @else
                <table class="tabel mt-2 text-[0.8125rem]">
                    <thead><tr><th scope="col">Pesanan</th><th scope="col">Tanggal</th><th scope="col">Orang tua / anak</th><th scope="col">Tutor</th><th scope="col">Mapel</th><th scope="col">Status</th><th scope="col" class="text-right">Total</th></tr></thead>
                    <tbody>
                        @foreach ($daftarPesanan as $pesanan)
                            <tr>
                                <td class="angka">{{ $pesanan->id_pesanan }}</td>
                                <td class="whitespace-nowrap">{{ Format::tanggalPendek($pesanan->created_at) }}</td>
                                <td>{{ $pesanan->siswa->wali->nama }} / {{ $pesanan->siswa->nama }}</td>
                                <td>{{ $pesanan->tutor->nama }}</td>
                                <td>{{ $pesanan->mapel->label() }}</td>
                                <td>{{ $pesanan->status->label() }}</td>
                                <td class="angka text-right">{{ Format::rupiah($pesanan->total_biaya) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <p class="mt-8 text-[0.8125rem] text-pensil-muda">Pesanan dihitung menurut tanggal dibuat, transaksi menurut tanggal pembayaran diproses, komisi menurut tanggal pembayaran (sudah disesuaikan bila ada pengembalian), dan pengembalian menurut tanggal pembatalan.</p>
    </main>
</x-layouts.app>
