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
        <meta name="theme-color" content="#fdfdf9">

        <title>{{ $title ? $title.' | EduLink Tutor' : 'EduLink Tutor: guru les privat di Kota Jambi' }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <a href="#konten" class="lewati">Lewati ke konten utama</a>

        {{ $slot }}
    </body>
</html>
