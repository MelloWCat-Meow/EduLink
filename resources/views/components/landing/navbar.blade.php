@php
    $links = [
        '#cara-kerja' => 'Cara kerja',
        '#mode-belajar' => 'Mode belajar',
        '#contoh-tutor' => 'Contoh tutor',
        '#untuk-tutor' => 'Untuk tutor',
        '#faq' => 'Tanya jawab',
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-garis bg-white">
    <nav aria-label="Navigasi utama" class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
        <a href="{{ route('landing') }}" class="text-lg font-extrabold tracking-tight text-dongker">
            EduLink <span class="font-semibold">Tutor</span>
        </a>

        <ul class="hidden items-center gap-1 lg:flex">
            @foreach ($links as $href => $label)
                <li>
                    <a href="{{ $href }}" class="rounded-md px-3 py-2 text-sm font-medium text-dongker hover:bg-kertas hover:underline">{{ $label }}</a>
                </li>
            @endforeach
        </ul>

        <div class="hidden items-center gap-2 lg:flex">
            <a href="{{ route('login') }}" class="rounded-md px-4 py-2.5 text-sm font-semibold text-dongker hover:bg-kertas">Masuk</a>
            <a href="{{ route('register') }}" class="rounded-md bg-dongker px-4 py-2.5 text-sm font-semibold text-white hover:bg-dongker-muda active:bg-dongker">Daftar</a>
        </div>

        <button
            type="button"
            data-menu-toggle
            aria-controls="menu-ponsel"
            aria-expanded="true"
            class="inline-flex min-h-11 items-center gap-2 rounded-md border border-garis px-3 text-sm font-semibold text-dongker hover:bg-kertas active:bg-garis lg:hidden"
        >
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square">
                <path d="M2 4.5h14M2 9h14M2 13.5h14" />
            </svg>
            <span data-menu-label>Menu</span>
        </button>
    </nav>

    <div id="menu-ponsel" class="border-t border-garis bg-white lg:hidden">
        <ul class="mx-auto max-w-6xl px-4 py-2 sm:px-6">
            @foreach ($links as $href => $label)
                <li>
                    <a href="{{ $href }}" class="flex min-h-12 items-center border-b border-kertas text-base font-medium text-dongker hover:underline">{{ $label }}</a>
                </li>
            @endforeach
        </ul>
        <div class="mx-auto grid max-w-6xl grid-cols-2 gap-3 px-4 pt-2 pb-4 sm:px-6">
            <a href="{{ route('login') }}" class="flex min-h-12 items-center justify-center rounded-md border border-dongker font-semibold text-dongker hover:bg-kertas">Masuk</a>
            <a href="{{ route('register') }}" class="flex min-h-12 items-center justify-center rounded-md bg-dongker font-semibold text-white hover:bg-dongker-muda">Daftar</a>
        </div>
    </div>
</header>
