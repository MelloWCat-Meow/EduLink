@props(['sesi', 'pesanan', 'untukTutor' => false])

@use('App\Enums\ModeBelajar')
@use('App\Enums\StatusPesanan')

{{--
    Where a session takes place, for paid orders only: the tutor's meeting link
    (session link first, else the order's), a plain note when the tutor has not
    shared one yet, or "kunjungan rumah". Nothing for unpaid orders.
--}}
@if ($pesanan->status === StatusPesanan::Dibayar)
    @if ($pesanan->mode !== ModeBelajar::Online)
        <span {{ $attributes->merge(['class' => 'text-[0.9375rem] text-pensil-muda']) }}>Kunjungan rumah</span>
    @elseif ($tautan = $sesi->tautanBerlaku($pesanan))
        <a href="{{ $tautan }}" target="_blank" rel="noopener" {{ $attributes->merge(['class' => 'tautan']) }}>Buka ruang meeting<span class="sr-only"> ({{ parse_url($tautan, PHP_URL_HOST) }}, tab baru)</span></a>
    @else
        <span {{ $attributes->merge(['class' => 'text-[0.9375rem] text-pensil-muda']) }}>
            @if ($untukTutor)
                Tautan meeting belum diisi.
            @else
                Tutor belum membagikan tautan meeting. Tanyakan lewat tombol WhatsApp di panel Tutor.
            @endif
        </span>
    @endif
@endif
