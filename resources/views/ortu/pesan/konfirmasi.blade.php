@use('App\Support\Format')

@php($jam = rtrim(rtrim(number_format($data['durasi'] / 60, 1, ',', ''), '0'), ','))

<x-dasbor.layout title="Periksa pesanan">
    <x-dasbor.judul title="Periksa pesanan" note="belum terkirim sampai Anda menekan Konfirmasi" />

    <x-dasbor.panel heading="Ringkasan" class="max-w-2xl">
        <dl class="grid gap-x-6 gap-y-3 sm:grid-cols-2">
            <div><dt class="text-[0.875rem] text-pensil-muda">Anak</dt><dd class="font-bold">{{ $data['siswa']->nama }} ({{ $data['siswa']->jenjang }})</dd></div>
            <div><dt class="text-[0.875rem] text-pensil-muda">Tutor</dt><dd class="font-bold">{{ $tutor->nama }}</dd></div>
            <div><dt class="text-[0.875rem] text-pensil-muda">Mapel</dt><dd class="font-bold">{{ $data['mapel']->label() }}</dd></div>
            <div><dt class="text-[0.875rem] text-pensil-muda">Cara belajar</dt><dd class="font-bold">{{ $data['mode']->label() }}</dd></div>
            <div><dt class="text-[0.875rem] text-pensil-muda">Jadwal mingguan</dt><dd class="font-bold">{{ $jadwal->label() }} WIB, {{ $data['durasi'] }} menit</dd></div>
            <div><dt class="text-[0.875rem] text-pensil-muda">Sesi pertama</dt><dd class="font-bold">{{ Format::tanggal($data['tanggal_mulai'], true) }}</dd></div>
        </dl>

        <div class="mt-5 border-t-2 border-dashed border-pensil/25 pt-4">
            <p class="text-[0.9375rem]"><span class="angka">{{ Format::rupiah($tutor->profil->tarif) }}</span> per jam × {{ $jam }} jam × {{ $data['jumlah_pertemuan'] }} pertemuan</p>
            <p class="mt-1 flex items-baseline justify-between gap-4">
                <span class="font-bold">Total</span>
                <span class="angka font-display text-[1.8rem] font-extrabold"><span class="stabilo">{{ Format::rupiah($total) }}</span></span>
            </p>
        </div>

        <p class="mt-4 text-[0.9375rem]">Jadwal ini ditahan untuk Anda selama tutor memutuskan (paling lama {{ \App\Services\AlurPesanan::MASA_TAHAN_JAM }} jam). Pembayaran baru diminta setelah tutor menerima.</p>

        @php($isian = ['id_siswa' => $data['siswa']->id_siswa, 'kode_mapel' => $data['mapel']->kode_mapel, 'id_jadwal' => $data['id_jadwal'], 'mode' => $data['mode']->value, 'durasi' => $data['durasi'], 'tanggal_mulai' => $data['tanggal_mulai']->toDateString(), 'jumlah_pertemuan' => $data['jumlah_pertemuan']])

        <div class="mt-6 flex flex-wrap items-center gap-4">
            <form method="post" action="{{ route('tutor.pesan.store', $tutor) }}">
                @csrf
                @foreach ($isian as $nama => $nilai)
                    <input type="hidden" name="{{ $nama }}" value="{{ $nilai }}">
                @endforeach
                <input type="hidden" name="konfirmasi" value="1">
                <button type="submit" class="tombol">Konfirmasi pesanan</button>
            </form>
            <a href="{{ route('tutor.pesan', ['tutor' => $tutor, ...$isian]) }}" class="tautan">Ubah isian</a>
        </div>
    </x-dasbor.panel>
</x-dasbor.layout>
