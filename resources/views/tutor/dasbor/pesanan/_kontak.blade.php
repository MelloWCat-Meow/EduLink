{{-- Contact with the parent. The WhatsApp link comes from KontakWhatsApp: paid orders, or finished in the last 14 days. --}}
<x-dasbor.panel heading="Orang tua">
    <p class="font-bold">{{ $pesanan->siswa->wali->nama }}</p>
    <p class="text-[0.9375rem] text-pensil-muda">wali dari {{ $pesanan->siswa->nama }} ({{ $pesanan->siswa->jenjang }})</p>
    <x-dasbor.tombol-whatsapp :kontak="$kontak" class="mt-4" />
</x-dasbor.panel>
