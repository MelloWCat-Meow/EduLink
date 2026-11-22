@props(['data', 'periode'])

{{--
    Orders per day as a single-series column chart (server-rendered SVG).
    One hue, thin columns with a rounded top, hairline grid; each column has a
    hover title and the same numbers are in the table below.
--}}
@php
    $hari = count($data);
    $maks = max($data) ?: 0;
    $langkah = max(1, (int) ceil($maks / 4));
    // At least 0-4 on the axis, so a quiet month does not look like a busy one.
    $atas = max(4, $langkah * (int) ceil($maks / $langkah));
    $kiri = 40; $slot = 24; $lebarBatang = 14; $tinggiPlot = 150; $puncak = 18;
    $lebar = $kiri + $hari * $slot + 8;
    $y = fn (float $nilai) => $puncak + $tinggiPlot - ($nilai / $atas) * $tinggiPlot;
    $bulan = \App\Support\Format::periode($periode);
    $hariPuncak = $maks > 0 ? array_search($maks, $data, true) : null;
@endphp

<figure {{ $attributes }}>
    <div class="overflow-x-auto">
        <svg viewBox="0 0 {{ $lebar }} {{ $puncak + $tinggiPlot + 34 }}" class="block h-auto min-w-[40rem] w-full" role="img" aria-labelledby="grafik-judul">
            <title id="grafik-judul">Pesanan per hari, {{ $bulan }}. Rinciannya ada di tabel di bawah grafik.</title>

            @for ($tik = 0; $tik <= $atas; $tik += $langkah)
                <line x1="{{ $kiri }}" x2="{{ $lebar - 4 }}" y1="{{ $y($tik) }}" y2="{{ $y($tik) }}" stroke="rgb(37 37 43 / 0.14)" stroke-width="1" />
                <text x="{{ $kiri - 8 }}" y="{{ $y($tik) + 4 }}" text-anchor="end" class="fill-pensil-muda text-[14px]">{{ $tik }}</text>
            @endfor

            @foreach ($data as $tanggal => $jumlah)
                @php
                    $x = $kiri + ($tanggal - 1) * $slot + ($slot - $lebarBatang) / 2;
                    $tinggi = $jumlah / $atas * $tinggiPlot;
                    $dasar = $puncak + $tinggiPlot;
                    $r = min(4, $tinggi);
                @endphp
                <g class="group">
                    <title>{{ $tanggal }} {{ $bulan }}: {{ $jumlah }} pesanan</title>
                    {{-- Hit area wider and taller than the column. --}}
                    <rect x="{{ $kiri + ($tanggal - 1) * $slot }}" y="{{ $puncak }}" width="{{ $slot }}" height="{{ $tinggiPlot }}" fill="transparent" class="group-hover:fill-langit/50" />
                    @if ($jumlah > 0)
                        <path fill="var(--color-grafik)" d="M{{ $x }},{{ $dasar }} V{{ $dasar - $tinggi + $r }} Q{{ $x }},{{ $dasar - $tinggi }} {{ $x + $r }},{{ $dasar - $tinggi }} H{{ $x + $lebarBatang - $r }} Q{{ $x + $lebarBatang }},{{ $dasar - $tinggi }} {{ $x + $lebarBatang }},{{ $dasar - $tinggi + $r }} V{{ $dasar }} Z" />
                    @endif
                    @if ($tanggal === $hariPuncak)
                        <text x="{{ $x + $lebarBatang / 2 }}" y="{{ $dasar - $tinggi - 6 }}" text-anchor="middle" class="fill-pensil text-[15px] font-bold">{{ $jumlah }}</text>
                    @endif
                    @if ($tanggal === 1 || $tanggal % 5 === 0 || $tanggal === $hari)
                        <text x="{{ $x + $lebarBatang / 2 }}" y="{{ $dasar + 22 }}" text-anchor="middle" class="fill-pensil-muda text-[14px]">{{ $tanggal }}</text>
                    @endif
                </g>
            @endforeach

            <line x1="{{ $kiri }}" x2="{{ $lebar - 4 }}" y1="{{ $puncak + $tinggiPlot }}" y2="{{ $puncak + $tinggiPlot }}" stroke="rgb(37 37 43 / 0.45)" stroke-width="1" />
        </svg>
    </div>
    <figcaption class="mt-2 text-[0.875rem] text-pensil-muda">Sumbu bawah: tanggal. Sumbu kiri: jumlah pesanan yang dibuat hari itu.</figcaption>

    <details class="mt-3">
        <summary class="tautan inline-block">Lihat sebagai tabel</summary>
        <div class="mt-2 max-h-72 overflow-auto">
            <table class="tabel">
                <thead><tr><th scope="col">Tanggal</th><th scope="col" class="text-right">Pesanan</th></tr></thead>
                <tbody>
                    @foreach ($data as $tanggal => $jumlah)
                        <tr><td>{{ $tanggal }} {{ $bulan }}</td><td class="angka text-right">{{ $jumlah }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
</figure>
