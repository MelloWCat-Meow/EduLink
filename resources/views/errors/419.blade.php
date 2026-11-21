<x-galat kode="419" judul="Halaman ini sudah terlalu lama terbuka." catatan="formulir perlu dimuat ulang"
    pesan="Demi keamanan, formulir yang dibiarkan terbuka lama tidak bisa dikirim. Muat ulang halaman sebelumnya, lalu isi dan kirim lagi. Isian yang belum terkirim mungkin perlu ditulis ulang.">
    <a href="{{ url()->previous() }}" class="inline-flex min-h-12 items-center rounded-[0.7rem_1.1rem_0.6rem_1.2rem] bg-tinta px-6 font-bold text-white">Kembali dan muat ulang</a>
    <button type="button" onclick="history.back()" class="inline-flex min-h-11 items-center font-bold underline decoration-2 underline-offset-4">Kembali tanpa memuat ulang</button>
</x-galat>
