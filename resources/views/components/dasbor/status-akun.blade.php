@props(['akun', 'pesananDibayar'])

@use('App\Support\Format')

{{-- Switch a parent or tutor account off or on, with the required reason and the full history. --}}
<x-dasbor.panel heading="Status akun" heading-id="status-akun" {{ $attributes }}>
    <p class="flex flex-wrap items-center gap-3">
        <x-dasbor.label :tone="$akun->aktif ? 'baik' : 'buruk'">{{ $akun->aktif ? 'Aktif' : 'Nonaktif' }}</x-dasbor.label>
        <span class="text-[0.9375rem] text-pensil-muda">{{ $akun->id_akun }} · {{ $akun->kontak }}</span>
    </p>
    @unless ($akun->aktif)
        <p class="mt-2 text-[0.9375rem]">Dinonaktifkan {{ Format::waktu($akun->dinonaktifkan_pada) }}. Alasan: {{ $akun->alasan_nonaktif }}</p>
    @endunless

    @if ($pesananDibayar->isNotEmpty())
        <div role="note" class="mt-4 border-l-4 border-cap bg-jambu/50 px-3 py-2 text-[0.9375rem]">
            <p class="font-bold">{{ $pesananDibayar->count() }} pesanan sudah dibayar dan masih berjalan.</p>
            <p>Pesanan ini tidak dibatalkan otomatis. Kalau perlu dihentikan, batalkan satu per satu supaya pengembalian dana tercatat:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($pesananDibayar as $pesanan)
                    <li><a href="{{ route('admin.pesanan.show', $pesanan) }}" class="tautan angka">{{ $pesanan->id_pesanan }}</a> · {{ $pesanan->mapel->label() }} · {{ $pesanan->siswa->nama }} dengan {{ $pesanan->tutor->nama }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route($akun->aktif ? 'admin.pengguna.nonaktifkan' : 'admin.pengguna.aktifkan', $akun) }}" class="mt-5 space-y-3" novalidate>
        @csrf
        <x-dasbor.field name="alasan" :label="$akun->aktif ? 'Alasan menonaktifkan' : 'Alasan mengaktifkan kembali'"
            :hint="$akun->aktif ? 'Wajib. Akun langsung keluar dari semua perangkat dan tidak bisa masuk; pesanan yang menunggu jawaban atau pembayaran dibatalkan otomatis.' : 'Wajib. Tercatat di riwayat akun.'">
            <textarea id="isian-alasan" name="alasan" maxlength="255" rows="2" class="kontrol" aria-describedby="isian-alasan-petunjuk @error('alasan') isian-alasan-galat @enderror" @error('alasan') aria-invalid="true" @enderror>{{ old('alasan') }}</textarea>
        </x-dasbor.field>
        <button type="submit" @class(['tombol-garis tombol-bahaya' => $akun->aktif, 'tombol' => ! $akun->aktif])>{{ $akun->aktif ? 'Nonaktifkan akun' : 'Aktifkan kembali' }}</button>
    </form>

    @if ($akun->riwayatStatus->isNotEmpty())
        <h3 class="mt-6 font-bold">Riwayat status</h3>
        <ol class="mt-2 space-y-2 text-[0.9375rem]">
            @foreach ($akun->riwayatStatus as $riwayat)
                <li class="border-l-2 border-pensil/25 pl-3">
                    <span class="font-bold">{{ $riwayat->aktif ? 'Diaktifkan' : 'Dinonaktifkan' }}</span>
                    oleh {{ $riwayat->admin?->nama ?? 'admin' }}, {{ Format::waktu($riwayat->dicatat_pada) }}.
                    <span class="block">Alasan: {{ $riwayat->alasan }}</span>
                </li>
            @endforeach
        </ol>
    @endif
</x-dasbor.panel>
