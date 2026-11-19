@props([
    'title' => null,
    'description' => 'Cari dan pesan guru les privat SD, SMP, dan SMA di Kota Jambi. Tutor terverifikasi, tarif terlihat sebelum memesan, belajar online atau tutor datang ke rumah.',
])

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ $description }}">

        <title>{{ $title ? $title.' | EduLink Tutor' : 'EduLink Tutor' }}</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen">
        <a
            href="#konten"
            class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-md focus:bg-dongker focus:px-4 focus:py-3 focus:text-white"
        >
            Lewati ke konten utama
        </a>

        {{ $slot }}
    </body>
</html>
