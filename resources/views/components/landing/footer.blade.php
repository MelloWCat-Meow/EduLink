@props(['contact'])

<footer class="di-gelap sobek-atas halaman -mt-4 bg-tinta-tua pt-16 pb-10 text-white">
    @php($adaKontak = $contact['address'] || $contact['whatsapp'])
    <div @class(['mx-auto grid max-w-[78rem] gap-10', 'md:grid-cols-[1.4fr_1fr_1fr]' => $adaKontak, 'md:grid-cols-[1.4fr_1fr]' => ! $adaKontak])>
        <div>
            <p class="font-display text-[1.6rem] leading-none font-extrabold [font-stretch:85%]">
                EduLink <span class="font-tangan text-[1.45rem] font-bold text-stabilo">tutor</span>
            </p>
            <p class="mt-3 max-w-[26rem] leading-[1.65] text-white/85">
                Layanan guru les privat dari PT EduLink Teknologi Indonesia, bimbingan belajar SD, SMP, dan SMA di Kota Jambi.
            </p>
        </div>

        @if ($adaKontak)
            <div>
                <h2 class="font-tangan text-[1.3rem] text-stabilo">Kontak</h2>
                <address class="mt-2 space-y-1 leading-[1.65] text-white/85 not-italic">
                    @if ($contact['address'])
                        <p>{{ $contact['address'] }}</p>
                    @endif
                    @if ($contact['whatsapp'])
                        <p><a href="https://wa.me/{{ $contact['whatsapp'] }}" class="inline-flex min-h-11 items-center font-bold text-white underline underline-offset-4">Chat admin di WhatsApp</a></p>
                    @endif
                </address>
            </div>
        @endif

        <div>
            <h2 class="font-tangan text-[1.3rem] text-stabilo">Informasi</h2>
            <ul class="mt-1">
                <li><a href="{{ route('privacy') }}" class="inline-flex min-h-11 items-center underline-offset-4 hover:underline">Kebijakan privasi</a></li>
                <li><a href="{{ route('syarat') }}" class="inline-flex min-h-11 items-center underline-offset-4 hover:underline">Syarat dan ketentuan</a></li>
                <li><a href="{{ route('tutor.register') }}" class="inline-flex min-h-11 items-center underline-offset-4 hover:underline">Daftar jadi tutor</a></li>
                <li><a href="{{ route('login') }}" class="inline-flex min-h-11 items-center underline-offset-4 hover:underline">Masuk</a></li>
            </ul>
        </div>
    </div>

    <p class="mx-auto mt-12 flex max-w-[78rem] flex-wrap justify-between gap-x-6 gap-y-1 border-t border-white/20 pt-5 text-[0.875rem] text-white/75">
        <span>&copy; {{ date('Y') }} PT EduLink Teknologi Indonesia, Kota Jambi</span>
        <span>EduLink Tutor {{ config('edulink.versi') }}</span>
    </p>
</footer>
