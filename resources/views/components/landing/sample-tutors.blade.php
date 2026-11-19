@props(['tutors'])

<section id="contoh-tutor" aria-labelledby="judul-contoh-tutor" class="border-y border-garis bg-kertas">
    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 md:py-16">
        <div class="md:flex md:items-end md:justify-between md:gap-8">
            <div>
                <h2 id="judul-contoh-tutor" class="text-2xl font-extrabold tracking-tight sm:text-3xl">Yang Anda lihat sebelum memesan</h2>
                <p class="mt-2 max-w-2xl text-abu">Setiap tutor menampilkan tarif per jam, jenjang, area mengajar, dan rating dari pemesan sebelumnya, jadi Anda bisa membandingkan lebih dulu.</p>
            </div>
            <a href="{{ route('tutor.index') }}" class="mt-4 inline-flex min-h-11 items-center font-semibold text-dongker underline underline-offset-4 hover:text-merah md:mt-0 md:shrink-0">Lihat semua tutor</a>
        </div>

        <p class="mt-6 inline-block rounded-md border border-dashed border-abu bg-white px-3 py-2 text-sm text-abu">
            Contoh tampilan. Nama, tarif, dan rating di bawah ini bukan data tutor sungguhan.
        </p>

        <ul class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($tutors as $tutor)
                <li><x-landing.tutor-card :tutor="$tutor" /></li>
            @endforeach
        </ul>
    </div>
</section>
