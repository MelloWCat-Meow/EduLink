@props(['sesi', 'pesanan'])

@use('App\Enums\StatusJadwalUlang')
@use('App\Enums\StatusPesanan')
@use('App\Services\JadwalUlang')
@use('App\Support\Format')
@use('Illuminate\Support\MessageBag')

{{--
    Rescheduling under one session, for the parent and the tutor alike. The
    service enforces every rule; this only hides actions that cannot succeed.
--}}
@php
    $akun = auth()->user();
    $permintaan = $sesi->permintaanJadwalUlang->sortByDesc('id');
    $menunggu = $permintaan->firstWhere('status', StatusJadwalUlang::Menunggu);
    $kaliDipindah = $permintaan->where('status', StatusJadwalUlang::Disetujui)->count();
    $bisaAjukan = $menunggu === null
        && $pesanan->status === StatusPesanan::Dibayar
        && $sesi->kehadiran === null
        && $kaliDipindah < JadwalUlang::MAKS_PER_SESI
        && now()->lessThanOrEqualTo($sesi->waktu->copy()->subHours(JadwalUlang::JAM_SEBELUM_SESI));
    $galat = old('untuk_sesi') === $sesi->id_sesi ? $errors->getBag('jadwalUlang') : new MessageBag;
    $idForm = 'jadwal-ulang-'.$sesi->id_sesi;
    $terakhirDijawab = $permintaan->first(fn ($p) => in_array($p->status, [StatusJadwalUlang::Ditolak, StatusJadwalUlang::Kedaluwarsa], true) && $p->updated_at->gt(now()->subDays(7)));
@endphp

<div {{ $attributes->merge(['class' => 'text-[0.9375rem]']) }}>
    @if ($kaliDipindah > 0)
        <p class="text-pensil-muda">Dijadwalkan ulang {{ $kaliDipindah }} kali{{ $kaliDipindah >= JadwalUlang::MAKS_PER_SESI ? ' (batas maksimal)' : '' }}.</p>
    @endif

    @if ($menunggu)
        <div class="mt-2 border-l-4 border-stabilo bg-sticky/60 px-3 py-2">
            @if ($menunggu->id_akun === $akun->id_akun)
                <p><span class="font-bold">Menunggu jawaban:</span> pindah ke {{ Format::waktu($menunggu->waktu_baru, true) }}.</p>
            @else
                <p><span class="font-bold">Diminta pindah ke {{ Format::waktu($menunggu->waktu_baru, true) }}.</span> Alasan: {{ $menunggu->alasan }}</p>
                <p class="text-pensil-muda">Kalau tidak dijawab sampai {{ Format::waktu($menunggu->waktu_lama) }}, sesi tetap pada waktu semula.</p>
                <div class="mt-2 flex flex-wrap items-start gap-3">
                    <form method="post" action="{{ route('jadwal-ulang.setujui', $menunggu) }}">
                        @csrf
                        <button type="submit" class="tombol min-h-10">Setujui jadwal baru</button>
                    </form>
                    <details class="group">
                        <summary class="tombol-garis tombol-bahaya min-h-10 list-none">Tolak</summary>
                        <form method="post" action="{{ route('jadwal-ulang.tolak', $menunggu) }}" class="mt-2 space-y-2" novalidate>
                            @csrf
                            <label for="tolak-{{ $menunggu->id }}" class="block font-bold">Alasan menolak</label>
                            <input id="tolak-{{ $menunggu->id }}" name="alasan" maxlength="255" required class="kontrol max-w-[22rem]">
                            <button type="submit" class="tombol-garis tombol-bahaya min-h-10">Kirim penolakan</button>
                        </form>
                    </details>
                </div>
            @endif
        </div>
    @elseif ($terakhirDijawab)
        <p class="mt-1 text-pensil-muda">Pengajuan jadwal ulang terakhir {{ mb_strtolower($terakhirDijawab->status->label()) }}{{ $terakhirDijawab->alasan_penolakan ? ': '.$terakhirDijawab->alasan_penolakan : '' }}.</p>
    @endif

    @if ($bisaAjukan)
        <details class="mt-2" @if ($galat->isNotEmpty()) open @endif>
            <summary class="tautan inline-flex min-h-11 list-none items-center">Ajukan jadwal ulang<span class="sr-only"> untuk sesi {{ Format::waktu($sesi->waktu) }}</span></summary>
            <form method="post" action="{{ route('jadwal-ulang.ajukan', $sesi) }}" class="mt-2 grid max-w-[34rem] gap-3 sm:grid-cols-2" novalidate>
                @csrf
                <input type="hidden" name="untuk_sesi" value="{{ $sesi->id_sesi }}">
                <div>
                    <label for="{{ $idForm }}-tanggal" class="block font-bold">Tanggal baru</label>
                    <input id="{{ $idForm }}-tanggal" type="date" name="tanggal" value="{{ $galat->isNotEmpty() ? old('tanggal') : '' }}"
                        min="{{ now()->addHours(JadwalUlang::JAM_MINIMAL_KE_DEPAN)->toDateString() }}" max="{{ $sesi->waktu->copy()->addDays(JadwalUlang::HARI_MAKSIMAL_MUNDUR)->toDateString() }}" class="kontrol"
                        @if ($galat->has('tanggal')) aria-invalid="true" aria-describedby="{{ $idForm }}-tanggal-galat" @endif>
                    @if ($galat->has('tanggal'))<p id="{{ $idForm }}-tanggal-galat" class="koreksi">{{ $galat->first('tanggal') }}</p>@endif
                </div>
                <div>
                    <label for="{{ $idForm }}-jam" class="block font-bold">Jam mulai</label>
                    <input id="{{ $idForm }}-jam" type="time" name="jam" value="{{ $galat->isNotEmpty() ? old('jam') : substr($pesanan->jadwal->jam, 0, 5) }}" min="06:00" max="21:00" step="900" class="kontrol"
                        @if ($galat->has('jam')) aria-invalid="true" aria-describedby="{{ $idForm }}-jam-galat" @endif>
                    @if ($galat->has('jam'))<p id="{{ $idForm }}-jam-galat" class="koreksi">{{ $galat->first('jam') }}</p>@endif
                </div>
                <div class="sm:col-span-2">
                    <label for="{{ $idForm }}-alasan" class="block font-bold">Alasan</label>
                    <input id="{{ $idForm }}-alasan" name="alasan" value="{{ $galat->isNotEmpty() ? old('alasan') : '' }}" maxlength="255" class="kontrol"
                        @if ($galat->has('alasan')) aria-invalid="true" aria-describedby="{{ $idForm }}-alasan-galat" @endif>
                    @if ($galat->has('alasan'))<p id="{{ $idForm }}-alasan-galat" class="koreksi">{{ $galat->first('alasan') }}</p>@endif
                </div>
                <p class="text-pensil-muda sm:col-span-2">Paling cepat {{ JadwalUlang::JAM_MINIMAL_KE_DEPAN }} jam dari sekarang, paling lambat {{ JadwalUlang::HARI_MAKSIMAL_MUNDUR }} hari setelah waktu semula. Sesi pindah setelah pihak lain menyetujui.</p>
                <div class="sm:col-span-2"><button type="submit" class="tombol-garis min-h-10">Kirim pengajuan</button></div>
            </form>
        </details>
    @endif
</div>
