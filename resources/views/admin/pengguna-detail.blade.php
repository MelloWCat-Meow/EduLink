@use('App\Enums\Peran')
@use('App\Support\Format')

<x-dasbor.layout :title="'Akun '.$akun->nama">
    <a href="{{ route('admin.pengguna.index') }}" class="tautan mb-4 inline-block">Kembali ke data pengguna</a>
    <x-dasbor.judul :title="$akun->nama" :note="$akun->peran->label().' · terdaftar '.Format::tanggal($akun->created_at)" />

    <div class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] lg:items-start">
        <x-dasbor.panel heading="Data akun">
            <dl class="grid gap-x-6 gap-y-3 sm:grid-cols-2">
                <div><dt class="text-[0.875rem] text-pensil-muda">Email</dt><dd class="font-bold break-all">{{ $akun->kontak }}</dd></div>
                <div><dt class="text-[0.875rem] text-pensil-muda">Nomor HP</dt><dd class="angka font-bold">{{ $akun->hasPeran(Peran::Tutor) ? ($akun->tutor?->no_hp ?? '–') : ($akun->no_hp ?? '–') }}</dd></div>
                <div><dt class="text-[0.875rem] text-pensil-muda">Jumlah pesanan</dt><dd class="angka font-bold">{{ $jumlahPesanan }}</dd></div>
                <div><dt class="text-[0.875rem] text-pensil-muda">Persetujuan kebijakan</dt><dd class="font-bold">{{ $akun->persetujuan_pada ? Format::tanggalPendek($akun->persetujuan_pada).' (versi '.$akun->versi_kebijakan.')' : '–' }}</dd></div>
                @if ($akun->hasPeran(Peran::Ortu))
                    <div class="sm:col-span-2"><dt class="text-[0.875rem] text-pensil-muda">Anak</dt><dd>{{ $akun->siswa->map(fn ($siswa) => $siswa->nama.' ('.$siswa->jenjang.')')->join(', ') ?: '–' }}</dd></div>
                @elseif ($akun->tutor)
                    <div class="sm:col-span-2"><dt class="text-[0.875rem] text-pensil-muda">Data tutor</dt><dd><a href="{{ route('admin.tutor.show', $akun->tutor) }}" class="tautan">{{ $akun->tutor->id_tutor }}, kelola tutor dan mata pelajaran</a></dd></div>
                @endif
            </dl>
        </x-dasbor.panel>

        <x-dasbor.status-akun :akun="$akun" :pesanan-dibayar="$pesananDibayar" />
    </div>
</x-dasbor.layout>
