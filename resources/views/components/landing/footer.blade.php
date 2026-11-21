@props(['contact'])

<footer class="di-gelap sobek-atas halaman -mt-4 bg-tinta-tua pt-16 pb-10 text-white">
    <div class="mx-auto grid max-w-[78rem] gap-10 md:grid-cols-[1.4fr_1fr_1fr]">
        <div>
            <p class="font-display text-[1.6rem] leading-none font-extrabold [font-stretch:85%]">
                EduLink <span class="font-tangan text-[1.45rem] font-bold text-stabilo">tutor</span>
            </p>
            <p class="mt-3 max-w-[26rem] leading-[1.65] text-white/85">
                Layanan guru les privat dari PT EduLink Teknologi Indonesia, bimbingan belajar SD, SMP, dan SMA di Kota Jambi.
            </p>
        </div>

        <div>
            <h2 class="font-tangan text-[1.3rem] text-stabilo">Kontak</h2>
            <address class="mt-2 space-y-1 leading-[1.65] text-white/85 not-italic">
                {{-- TODO: set EDULINK_ADDRESS and EDULINK_WHATSAPP in .env with the real values. --}}
                <p>{{ $contact['address'] ?? '[Alamat kantor], Kota Jambi' }}</p>
                @if ($contact['whatsapp'])
                    <p><a href="https://wa.me/{{ $contact['whatsapp'] }}" class="inline-flex min-h-11 items-center font-bold text-white underline underline-offset-4">Chat admin di WhatsApp</a></p>
                @else
                    <p>WhatsApp: [nomor belum diisi]</p>
                @endif
            </address>
        </div>

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

    <p class="mx-auto mt-12 max-w-[78rem] border-t border-white/20 pt-5 text-[0.875rem] text-white/75">
        &copy; {{ date('Y') }} PT EduLink Teknologi Indonesia, Kota Jambi
    </p>
</footer>
