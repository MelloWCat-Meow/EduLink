@props(['batas', 'awalan' => 'Sisa waktu menjawab'])

@use('App\Support\Format')

{{--
    Deadline countdown. The server writes the text, so it reads right without
    JavaScript; a small script keeps it current while the page stays open.
    Under three hours left it switches to the red-pen style and says so in words.
--}}
@php($mendesak = $batas && $batas->isFuture() && now()->diffInMinutes($batas) < 180)

<p {{ $attributes->merge(['class' => 'flex flex-wrap items-baseline gap-x-2']) }}>
    <span class="text-[0.875rem] text-pensil-muda">{{ $awalan }}</span>
    <time datetime="{{ $batas?->toIso8601String() }}" data-hitung-mundur @class(['angka font-display text-[1.35rem] font-extrabold', 'text-cap' => $mendesak])>{{ Format::sisaWaktu($batas) }}</time>
    @if ($mendesak)
        <span class="text-[0.875rem] font-bold text-cap">segera</span>
    @endif
    <span class="text-[0.875rem] text-pensil-muda">sampai {{ Format::waktu($batas) }}</span>
</p>

@once
    <script>
        (() => {
            const tulis = () => document.querySelectorAll('[data-hitung-mundur]').forEach((el) => {
                const sisa = Math.floor((new Date(el.getAttribute('datetime')) - Date.now()) / 60000);
                if (Number.isNaN(sisa)) return;
                if (sisa < 0) { el.textContent = 'sudah lewat'; return; }
                const jam = Math.floor(sisa / 60), menit = sisa % 60;
                el.textContent = `${jam > 0 ? jam + ' jam ' : ''}${menit > 0 || jam === 0 ? menit + ' menit' : ''}`.trim() + ' lagi';
            });
            setInterval(tulis, 30000);
        })();
    </script>
@endonce
