@php
    $links = [
        ['href' => '#cara-kerja', 'label' => 'Cara kerja'],
        ['href' => '#contoh-tutor', 'label' => 'Contoh tutor'],
        ['href' => '#tanya-jawab', 'label' => 'Tanya jawab'],
        ['href' => '#jadi-tutor', 'label' => 'Mau jadi tutor?'],
    ];
@endphp

<header class="halaman relative z-20 pt-4">
    <nav aria-label="Utama" class="mx-auto flex max-w-[78rem] items-center justify-between gap-4">
        <a href="{{ route('landing') }}" class="font-display text-[1.45rem] leading-none font-extrabold tracking-tight [font-stretch:85%]">
            EduLink <span class="font-tangan text-[1.35rem] font-bold text-tinta">tutor</span>
        </a>

        <ul class="hidden items-center gap-7 text-[1.0625rem] lg:flex">
            @foreach ($links as $link)
                <li><a href="{{ $link['href'] }}" class="chip-mapel py-2">{{ $link['label'] }}</a></li>
            @endforeach
            <li>
                <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center rounded-[0.6rem_0.9rem_0.5rem_1rem] border-2 border-pensil px-5 font-bold hover:bg-stabilo">
                    Masuk
                </a>
            </li>
        </ul>

        <details class="relative lg:hidden">
            <summary class="flex min-h-11 items-center rounded-[0.6rem_0.9rem_0.5rem_1rem] border-2 border-pensil px-4 font-bold">
                Menu
            </summary>
            <ul class="absolute right-0 mt-2 w-60 rotate-1 bg-sticky p-2 shadow-[0_12px_24px_-14px_rgb(0_0_0/0.45)]">
                @foreach ($links as $link)
                    <li><a href="{{ $link['href'] }}" class="block px-3 py-3 font-bold">{{ $link['label'] }}</a></li>
                @endforeach
                <li><a href="{{ route('login') }}" class="block px-3 py-3 font-bold">Masuk</a></li>
            </ul>
        </details>
    </nav>
</header>
