{{--
    Standalone page for 500 and 503: plain HTML, no components, no auth, no
    database, no config lookups beyond reading the built CSS file name. If
    even the manifest is missing, the inline styles still make it readable.
--}}
@php
    $css = null;
    try {
        $manifest = json_decode((string) @file_get_contents(public_path('build/manifest.json')), true);
        $css = isset($manifest['resources/css/app.css']['file']) ? '/build/'.$manifest['resources/css/app.css']['file'] : null;
    } catch (\Throwable) {
        $css = null;
    }
@endphp
<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <title>{{ $judul }} | EduLink Tutor</title>
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
        @if ($css)
            <link rel="stylesheet" href="{{ $css }}">
        @endif
        <style>
            body { margin: 0; font-family: system-ui, sans-serif; color: #25252b; background: #fdfdf9; }
            .galat-isi { max-width: 40rem; margin: 0 auto; padding: 4rem 1.25rem 4rem 2rem; }
            .galat-isi h1 { font-size: clamp(2rem, 6vw, 3.4rem); line-height: 1.05; margin: 1.5rem 0 1rem; }
            .galat-isi a.utama { display: inline-flex; min-height: 3rem; align-items: center; padding: 0 1.4rem; background: #1d2d8c; color: #fff; font-weight: 700; text-decoration: none; border-radius: 0.6rem; }
        </style>
    </head>
    <body>
        <main id="konten" class="galat-isi">
            <p class="font-display text-[1.45rem] font-extrabold">EduLink <span class="font-tangan text-tinta">tutor</span></p>
            <h1 class="font-display font-extrabold tracking-tight"><span class="stabilo">{{ $judul }}</span></h1>
            <p class="text-[1.0625rem] leading-[1.75rem]">{{ $pesan }}</p>
            <p class="mt-3 font-tangan text-[1.2rem] text-pensil-muda">Kode {{ $kode }}</p>
            <p style="margin-top:2rem"><a href="/" class="utama">Ke halaman utama</a></p>
        </main>
    </body>
</html>
