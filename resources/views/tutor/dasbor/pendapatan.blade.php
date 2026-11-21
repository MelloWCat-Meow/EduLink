@use('App\Support\Format')
@use('App\Enums\StatusPencairan')

@php($adaMenunggu = $riwayatPencairan->contains('status', StatusPencairan::Menunggu))

<x-dasbor.layout title="Pendapatan">
    <x-dasbor.judul title="Pendapatan dan pencairan" note="saldo = pembayaran berhasil − pengembalian dana − komisi − pencairan yang disetujui atau dibayar" />

    <div class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] lg:items-start">
        <div class="space-y-8">
            <x-dasbor.panel heading="Saldo tersedia">
                <p class="angka font-display text-[2.2rem] font-extrabold">{{ Format::rupiah($saldo['saldo']) }}</p>
                <dl class="mt-3 grid gap-x-6 gap-y-1 text-[0.9375rem] sm:grid-cols-3">
                    <div><dt class="text-pensil-muda">Pembayaran masuk (setelah pengembalian)</dt><dd class="angka font-bold">{{ Format::rupiah($saldo['pendapatan']) }}</dd></div>
                    <div><dt class="text-pensil-muda">Komisi EduLink</dt><dd class="angka font-bold">− {{ Format::rupiah($saldo['komisi']) }}</dd></div>
                    <div><dt class="text-pensil-muda">Sudah dicairkan</dt><dd class="angka font-bold">− {{ Format::rupiah($saldo['dicairkan']) }}</dd></div>
                </dl>
            </x-dasbor.panel>

            <x-dasbor.panel heading="Rincian per pesanan">
                @if ($rincian->isEmpty())
                    <x-dasbor.kosong message="Belum ada pesanan yang dibayar." />
                @else
                    <div class="overflow-x-auto">
                        <table class="tabel">
                            <thead><tr><th scope="col">Pesanan</th><th scope="col" class="text-right">Total</th><th scope="col" class="text-right">Dikembalikan</th><th scope="col" class="text-right">Komisi</th><th scope="col" class="text-right">Bersih</th></tr></thead>
                            <tbody>
                                @foreach ($rincian as $baris)
                                    <tr>
                                        <td><a href="{{ route('tutor.pesanan.show', $baris['pesanan']) }}" class="tautan angka">{{ $baris['pesanan']->id_pesanan }}</a><span class="block text-[0.8125rem] text-pensil-muda">{{ $baris['pesanan']->mapel->label() }} · {{ $baris['pesanan']->siswa->nama }} · {{ $baris['pesanan']->status->label() }}</span></td>
                                        <td class="angka text-right">{{ Format::rupiah($baris['total']) }}</td>
                                        <td class="angka text-right">{{ bccomp($baris['pengembalian'], '0', 2) === 1 ? '− '.Format::rupiah($baris['pengembalian']) : '–' }}</td>
                                        <td class="angka text-right">− {{ Format::rupiah($baris['komisi']) }}</td>
                                        <td class="angka text-right font-bold">{{ Format::rupiah($baris['bersih']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-dasbor.panel>

            <x-dasbor.panel heading="Riwayat pencairan">
                @if ($riwayatPencairan->isEmpty())
                    <x-dasbor.kosong message="Belum pernah mengajukan pencairan." />
                @else
                    <div class="overflow-x-auto">
                        <table class="tabel">
                            <thead><tr><th scope="col">Pengajuan</th><th scope="col">Periode</th><th scope="col" class="text-right">Nominal</th><th scope="col">Status</th></tr></thead>
                            <tbody>
                                @foreach ($riwayatPencairan as $pencairan)
                                    <tr>
                                        <td><span class="angka font-bold">{{ $pencairan->id_pencairan }}</span><span class="block text-[0.8125rem] text-pensil-muda">{{ Format::tanggalPendek($pencairan->created_at) }}</span></td>
                                        <td>{{ Format::periode($pencairan->periode) }}</td>
                                        <td class="angka text-right">{{ Format::rupiah($pencairan->nominal) }}</td>
                                        <td><x-dasbor.label :tone="$pencairan->status->tone()">{{ $pencairan->status->label() }}</x-dasbor.label></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-dasbor.panel>
        </div>

        <div class="space-y-8">
            <x-dasbor.panel heading="Ajukan pencairan" heading-id="ajukan">
                @if (! $tutor->punyaRekening())
                    <p class="border-l-4 border-cap bg-jambu/50 px-3 py-2">Isi rekening pencairan di bawah dulu, lalu ajukan pencairan.</p>
                @elseif ($adaMenunggu)
                    <p class="border-l-4 border-tinta bg-langit/60 px-3 py-2">Masih ada pengajuan yang menunggu persetujuan admin. Pengajuan baru bisa dibuat setelah itu diproses.</p>
                @else
                    <form method="post" action="{{ route('tutor.pendapatan.ajukan') }}" class="space-y-4" novalidate>
                        @csrf
                        <x-dasbor.field name="periode" label="Periode pendapatan">
                            <input id="isian-periode" type="month" name="periode" value="{{ old('periode', now()->format('Y-m')) }}" max="{{ now()->format('Y-m') }}" class="kontrol max-w-[12rem]" @error('periode') aria-invalid="true" aria-describedby="isian-periode-galat" @enderror>
                        </x-dasbor.field>
                        <x-dasbor.field name="nominal" label="Nominal (Rp)" :hint="'Maksimal '.Format::rupiah($saldo['saldo']).'.'">
                            <input id="isian-nominal" name="nominal" inputmode="numeric" value="{{ old('nominal') }}" class="kontrol max-w-[14rem]" aria-describedby="isian-nominal-petunjuk @error('nominal') isian-nominal-galat @enderror" @error('nominal') aria-invalid="true" @enderror>
                        </x-dasbor.field>
                        <p class="text-[0.9375rem]">Dikirim ke {{ $tutor->rekening_bank }} <span class="angka">{{ $tutor->rekeningTersamar() }}</span> a.n. {{ $tutor->rekening_atas_nama }}.</p>
                        <button type="submit" class="tombol">Ajukan pencairan</button>
                    </form>
                @endif
            </x-dasbor.panel>

            <x-dasbor.panel heading="Rekening pencairan" heading-id="rekening">
                @if ($tutor->punyaRekening())
                    <p class="mb-4">{{ $tutor->rekening_bank }} <span class="angka font-bold">{{ $tutor->rekeningTersamar() }}</span><span class="block text-[0.9375rem] text-pensil-muda">a.n. {{ $tutor->rekening_atas_nama }}</span></p>
                @endif
                <form method="post" action="{{ route('tutor.pendapatan.rekening') }}" class="space-y-4" novalidate>
                    @csrf
                    @method('PUT')
                    <x-dasbor.field name="rekening_bank" label="Nama bank">
                        <input id="isian-rekening_bank" name="rekening_bank" value="{{ old('rekening_bank', $tutor->rekening_bank) }}" maxlength="50" class="kontrol" @error('rekening_bank') aria-invalid="true" aria-describedby="isian-rekening_bank-galat" @enderror>
                    </x-dasbor.field>
                    <x-dasbor.field name="rekening_nomor" label="Nomor rekening" :hint="$tutor->punyaRekening() ? 'Tulis lengkap untuk mengganti; nomor lama tidak ditampilkan.' : null">
                        <input id="isian-rekening_nomor" name="rekening_nomor" inputmode="numeric" autocomplete="off" value="{{ old('rekening_nomor') }}" maxlength="25" class="kontrol" @if ($tutor->punyaRekening()) aria-describedby="isian-rekening_nomor-petunjuk" @endif @error('rekening_nomor') aria-invalid="true" @enderror>
                    </x-dasbor.field>
                    <x-dasbor.field name="rekening_atas_nama" label="Nama pemilik rekening">
                        <input id="isian-rekening_atas_nama" name="rekening_atas_nama" value="{{ old('rekening_atas_nama', $tutor->rekening_atas_nama) }}" maxlength="100" class="kontrol" @error('rekening_atas_nama') aria-invalid="true" aria-describedby="isian-rekening_atas_nama-galat" @enderror>
                    </x-dasbor.field>
                    <button type="submit" class="tombol-garis">{{ $tutor->punyaRekening() ? 'Ganti rekening' : 'Simpan rekening' }}</button>
                </form>
            </x-dasbor.panel>
        </div>
    </div>
</x-dasbor.layout>
