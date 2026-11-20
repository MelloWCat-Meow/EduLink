@use('App\Enums\LangkahPendaftaran')

<x-tutor.bagian nomor="5" judul="Periksa dan kirim">
    @if ($kekurangan !== [])
        <div role="alert" class="mb-8 border-l-4 border-cap bg-[rgb(192_38_45/0.05)] px-4 py-4">
            <p class="font-bold">Pendaftaran belum bisa dikirim. Yang masih kurang:</p>
            <ul class="mt-3 space-y-3">
                @foreach ($kekurangan as $kunci => $daftar)
                    @php($bagian = LangkahPendaftaran::from($kunci))
                    <li>
                        <a href="{{ route('tutor.pendaftaran.langkah', $bagian) }}" class="tautan">Langkah {{ $bagian->nomor() }}: {{ $bagian->label() }}</a>
                        <ul class="mt-1 list-disc pl-5 font-tangan text-[1.15rem] leading-snug text-cap">
                            @foreach ($daftar as $pesan)
                                <li>{{ $pesan }}</li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <p class="mb-8 max-w-[40rem] border-l-4 border-tinta bg-langit/60 px-4 py-3">Semua bagian sudah lengkap. Periksa sekali lagi, lalu kirim. Setelah dikirim, isian tidak bisa diubah sampai tim akademik selesai memeriksa.</p>
    @endif

    @include('tutor.pendaftaran._ringkasan', ['bisaUbah' => true])

    <form method="POST" action="{{ route('tutor.pendaftaran.kirim') }}" class="mt-10 border-t-2 border-dashed border-pensil/25 pt-6">
        @csrf
        @include('tutor.pendaftaran._tombol', ['teksTombol' => 'Kirim pendaftaran'])
    </form>
</x-tutor.bagian>
