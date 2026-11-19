@props(['contact'])

<footer class="on-dongker bg-dongker text-white">
    <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-[2fr_1fr_1fr]">
        <div>
            <p class="text-lg font-extrabold">EduLink <span class="font-semibold">Tutor</span></p>
            <p class="mt-2 max-w-sm text-sm leading-relaxed text-white/80">Layanan guru les privat dari PT EduLink Teknologi Indonesia, bimbingan belajar SD, SMP, dan SMA di Kota Jambi.</p>
        </div>

        <div>
            <h2 class="text-sm font-bold">Kontak</h2>
            <address class="mt-2 space-y-2 text-sm text-white/80 not-italic">
                {{-- TODO: set EDULINK_ADDRESS and EDULINK_WHATSAPP in .env with the real values. --}}
                <p>{{ $contact['address'] ?? '[Alamat kantor], Kota Jambi' }}</p>
                @if ($contact['whatsapp'])
                    <p><a href="https://wa.me/{{ $contact['whatsapp'] }}" class="inline-flex min-h-11 items-center font-semibold text-white underline underline-offset-4">WhatsApp admin</a></p>
                @else
                    <p>WhatsApp: [nomor belum diisi]</p>
                @endif
            </address>
        </div>

        <div>
            <h2 class="text-sm font-bold">Informasi</h2>
            <ul class="mt-1 text-sm">
                <li><a href="{{ route('privacy') }}" class="inline-flex min-h-11 items-center text-white/90 underline-offset-4 hover:underline">Kebijakan privasi</a></li>
                <li><a href="{{ route('tutor.register') }}" class="inline-flex min-h-11 items-center text-white/90 underline-offset-4 hover:underline">Daftar jadi tutor</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-white/15">
        <p class="mx-auto max-w-6xl px-4 py-4 text-xs text-white/80 sm:px-6">&copy; {{ date('Y') }} PT EduLink Teknologi Indonesia</p>
    </div>
</footer>
