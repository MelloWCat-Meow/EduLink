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

        @if (config('edulink.mode_demo'))
            {{-- So nobody mistakes a demo payment for a real one. --}}
            <p role="note" class="relative z-30 bg-pensil print:hidden px-4 py-1.5 text-center text-[0.8125rem] font-bold text-white">
                <span class="mr-1.5 rounded-sm bg-stabilo px-1.5 py-px text-pensil">Mode demo</span> pembayaran disimulasikan, tidak ada uang sungguhan yang berpindah.
            </p>
        @endif

        {{ $slot }}
    </body>
</html>
