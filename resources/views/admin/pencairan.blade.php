@use('App\Support\Format')
@use('App\Enums\StatusPencairan')

<x-dasbor.layout title="Pencairan dana">
    <x-dasbor.judul title="Pencairan dana" note="saldo = pembayaran berhasil − komisi − pencairan yang sudah disetujui atau dibayar" />

    <x-dasbor.panel>
        @if ($daftarPencairan->isEmpty())
            <x-dasbor.kosong message="Belum ada pengajuan pencairan dari tutor." />
        @else
            <div class="overflow-x-auto">
                <table class="tabel">
                    <thead>
                        <tr><th scope="col">Nomor</th><th scope="col">Tutor dan rekening</th><th scope="col">Periode</th><th scope="col" class="text-right">Diajukan</th><th scope="col" class="text-right">Saldo tutor</th><th scope="col">Status</th><th scope="col"><span class="sr-only">Aksi</span></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($daftarPencairan as $pencairan)
                            @php($s = $saldo[$pencairan->id_tutor])
                            <tr>
                                <td class="angka">{{ $pencairan->id_pencairan }}<span class="block text-[0.8125rem] text-pensil-muda">{{ Format::tanggalPendek($pencairan->created_at) }}</span></td>
                                <td>{{ $pencairan->tutor->nama }}<span class="block text-[0.8125rem] text-pensil-muda">@if ($pencairan->tutor->punyaRekening()){{ $pencairan->tutor->rekening_bank }} <span class="angka">{{ $pencairan->tutor->rekening_nomor }}</span> a.n. {{ $pencairan->tutor->rekening_atas_nama }}@else Rekening belum diisi @endif</span></td>
                                <td class="whitespace-nowrap">{{ Format::periode($pencairan->periode) }}</td>
                                <td class="angka text-right font-bold">{{ Format::rupiah($pencairan->nominal) }}</td>
                                <td class="angka text-right">
                                    {{ Format::rupiah($s['saldo']) }}
                                    <span class="block text-[0.8125rem] font-normal text-pensil-muda">{{ Format::rupiah($s['pendapatan']) }} − {{ Format::rupiah($s['komisi']) }} − {{ Format::rupiah($s['dicairkan']) }}</span>
                                </td>
                                <td><x-dasbor.label :tone="$pencairan->status->tone()">{{ $pencairan->status->label() }}</x-dasbor.label></td>
                                <td>
                                    @if ($pencairan->status === StatusPencairan::Menunggu)
                                        <form method="post" action="{{ route('admin.pencairan.setujui', $pencairan) }}">
                                            @csrf
                                            <button type="submit" class="tombol min-h-10" @disabled(bccomp((string) $pencairan->nominal, $s['saldo'], 2) === 1)>Setujui<span class="sr-only"> {{ $pencairan->id_pencairan }}</span></button>
                                        </form>
                                        @if (bccomp((string) $pencairan->nominal, $s['saldo'], 2) === 1)
                                            <p class="mt-1 text-[0.8125rem] text-cap">Saldo tidak cukup.</p>
                                        @endif
                                    @elseif ($pencairan->status === StatusPencairan::Disetujui)
                                        <form method="post" action="{{ route('admin.pencairan.bayar', $pencairan) }}">
                                            @csrf
                                            <button type="submit" class="tombol-garis min-h-10">Tandai dibayar<span class="sr-only"> {{ $pencairan->id_pencairan }}</span></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $daftarPencairan->links('components.dasbor.halaman') }}
        @endif
    </x-dasbor.panel>
</x-dasbor.layout>
