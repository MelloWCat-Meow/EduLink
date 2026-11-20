@use('App\Support\Format')

@php
    $akun = auth()->user();
    $layout = $akun?->peran === \App\Enums\Peran::Ortu ? 'dasbor.layout' : 'publik.layout';
@endphp

<x-dynamic-component :component="$layout" :title="$tutor->nama">
    <a href="{{ route('tutor.index') }}" class="tautan mb-4 inline-block">Kembali ke hasil pencarian</a>

    <x-dasbor.judul :title="$tutor->nama" :note="$tutor->status === \App\Enums\StatusTutor::Internal ? 'tutor EduLink' : 'berkas KTP, ijazah, dan sertifikat sudah diperiksa'">
        <x-slot:actions>
            @if (! $akun || $akun->peran === \App\Enums\Peran::Ortu)
                <a href="{{ route('tutor.pesan', $tutor) }}" class="tombol">{{ $akun ? 'Pesan tutor ini' : 'Masuk untuk memesan' }}</a>
            @endif
        </x-slot:actions>
    </x-dasbor.judul>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] lg:items-start">
        <div class="space-y-8">
            <x-dasbor.panel heading="Profil">
                <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div><dt class="text-[0.875rem] text-pensil-muda">Tarif</dt><dd><span class="angka font-display text-[1.4rem] font-extrabold">{{ Format::rupiah($tutor->profil->tarif) }}</span> per jam</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Rating</dt><dd><x-tutor.rating :rata="$tutor->rata_rating" :jumlah="$tutor->jumlah_ulasan" /></dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Kecamatan</dt><dd class="font-bold">{{ $tutor->profil->lokasi }}</dd></div>
                    <div><dt class="text-[0.875rem] text-pensil-muda">Cara belajar</dt><dd class="font-bold">{{ $tutor->profil->modesLabel() }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-[0.875rem] text-pensil-muda">Kompetensi</dt><dd>{{ $tutor->profil->kompetensi }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-[0.875rem] text-pensil-muda">Pengalaman</dt><dd>{{ $tutor->profil->pengalaman }}</dd></div>
                </dl>
            </x-dasbor.panel>

            <x-dasbor.panel heading="Ulasan orang tua">
                @if ($ulasan->isEmpty())
                    <x-dasbor.kosong message="Belum ada ulasan. Ulasan hanya bisa ditulis oleh orang tua yang sudah menyelesaikan sesi." />
                @else
                    <ul class="divide-y divide-pensil/12">
                        @foreach ($ulasan as $item)
                            <li class="py-3.5">
                                <p class="flex flex-wrap items-baseline justify-between gap-2">
                                    <span class="font-bold">{{ $item->nilai }} dari 5</span>
                                    <span class="text-[0.875rem] text-pensil-muda">{{ $item->pesanan->mapel->label() }} · {{ Format::tanggal($item->created_at) }}</span>
                                </p>
                                @if ($item->komentar)
                                    <p class="mt-1">{{ $item->komentar }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-dasbor.panel>
        </div>

        <div class="space-y-8">
            <x-dasbor.panel heading="Mapel">
                <ul class="flex flex-wrap gap-2">
                    @foreach ($tutor->mataPelajaran as $mapel)
                        <li class="rounded-full border-2 border-pensil/20 px-3 py-1 font-bold">{{ $mapel->label() }}</li>
                    @endforeach
                </ul>
            </x-dasbor.panel>

            <x-dasbor.panel heading="Jadwal yang masih kosong">
                @if ($jadwalTersedia->isEmpty())
                    <x-dasbor.kosong message="Semua jadwal tutor ini sedang terisi." />
                @else
                    <ul class="divide-y divide-pensil/12">
                        @foreach ($jadwalTersedia as $jadwal)
                            <li class="flex justify-between py-2.5"><span class="font-bold">{{ $jadwal->hari->label() }}</span><span class="angka">{{ $jadwal->jamLabel() }} WIB</span></li>
                        @endforeach
                    </ul>
                @endif
                <p class="mt-3 text-[0.875rem] text-pensil-muda">
                    Jadwal berulang tiap minggu.
                    @if ($jumlahJadwalTerisi > 0)
                        {{ $jumlahJadwalTerisi }} jadwal lain sedang dipakai orang tua lain.
                    @endif
                </p>
            </x-dasbor.panel>
        </div>
    </div>
</x-dynamic-component>
