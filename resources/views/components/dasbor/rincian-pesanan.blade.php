@props(['pesanan', 'untukTutor' => false])

@use('App\Support\Format')

@php
    $jam = rtrim(rtrim(number_format($pesanan->durasi / 60, 1, ',', ''), '0'), ',');
@endphp

<dl class="grid gap-x-6 gap-y-3 sm:grid-cols-2">
    <div><dt class="text-[0.875rem] text-pensil-muda">Anak</dt><dd class="font-bold">{{ $pesanan->siswa->nama }} ({{ $pesanan->siswa->jenjang }})</dd></div>
    @if ($untukTutor)
        <div><dt class="text-[0.875rem] text-pensil-muda">Jumlah pertemuan</dt><dd class="font-bold">{{ $pesanan->jumlah_pertemuan }} kali</dd></div>
    @else
        <div><dt class="text-[0.875rem] text-pensil-muda">Tutor</dt><dd class="font-bold">{{ $pesanan->tutor->nama }}</dd></div>
    @endif
    <div><dt class="text-[0.875rem] text-pensil-muda">Mapel</dt><dd class="font-bold">{{ $pesanan->mapel->label() }}</dd></div>
    <div><dt class="text-[0.875rem] text-pensil-muda">Cara belajar</dt><dd class="font-bold">{{ $pesanan->mode->label() }}</dd></div>
    <div><dt class="text-[0.875rem] text-pensil-muda">Jadwal mingguan</dt><dd class="font-bold">{{ $pesanan->jadwal->label() }} WIB, {{ $pesanan->durasi }} menit</dd></div>
    <div><dt class="text-[0.875rem] text-pensil-muda">Mulai</dt><dd class="font-bold">{{ Format::tanggal($pesanan->tanggal_mulai, true) }}</dd></div>
</dl>

<div class="mt-5 border-t-2 border-dashed border-pensil/25 pt-4">
    <p class="text-[0.9375rem]">
        <span class="angka">{{ Format::rupiah($pesanan->tarif_per_jam) }}</span> per jam × {{ $jam }} jam × {{ $pesanan->jumlah_pertemuan }} pertemuan
    </p>
    <p class="mt-1 flex items-baseline justify-between gap-4">
        <span class="font-bold">Total</span>
        <span class="angka font-display text-[1.6rem] font-extrabold">{{ Format::rupiah($pesanan->total_biaya) }}</span>
    </p>
    {{ $slot }}
</div>
