@use('App\Support\Format')
@use('App\Http\Requests\Ortu\PesanTutorRequest')

@php
    // Earliest valid start date (tomorrow or later) for each weekly slot.
    $mulaiTercepat = $jadwalTersedia->mapWithKeys(function ($jadwal) {
        $tanggal = now()->addDay()->startOfDay();
        while ($tanggal->isoWeekday() !== $jadwal->hari->isoWeekday()) {
            $tanggal->addDay();
        }

        return [$jadwal->id_jadwal => $tanggal];
    });
    $isi = fn (string $nama, mixed $bawaan = null) => old($nama, request()->query($nama, $bawaan));
@endphp

<x-dasbor.layout :title="'Pesan '.$tutor->nama">
    <a href="{{ route('tutor.show', $tutor) }}" class="tautan mb-4 inline-block">Kembali ke profil {{ $tutor->nama }}</a>
    <x-dasbor.judul :title="'Pesan '.$tutor->nama" :note="Format::rupiah($tutor->profil->tarif).' per jam'" />

    @if ($daftarAnak->isEmpty())
        <x-dasbor.panel>
            <x-dasbor.kosong message="Tambahkan data anak dulu. Setiap pesanan dibuat untuk seorang anak.">
                <a href="{{ route('anak.index') }}" class="tombol">Tambah anak</a>
            </x-dasbor.kosong>
        </x-dasbor.panel>
    @elseif ($jadwalTersedia->isEmpty())
        <x-dasbor.panel>
            <x-dasbor.kosong message="Semua jadwal tutor ini sedang terisi. Coba tutor lain atau cek lagi nanti.">
                <a href="{{ route('tutor.index') }}" class="tombol-garis">Cari tutor lain</a>
            </x-dasbor.kosong>
        </x-dasbor.panel>
    @else
        <form method="post" action="{{ route('tutor.pesan.store', $tutor) }}" novalidate
              class="grid grid-cols-[minmax(0,1fr)] gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] lg:items-start"
              data-tarif="{{ (float) $tutor->profil->tarif }}">
            @csrf

            <x-dasbor.panel heading="Isi pesanan">
                @if ($errors->any())
                    <p role="alert" class="mb-5 border-l-4 border-cap bg-jambu/50 px-3 py-2 font-bold">
                        Ada {{ count($errors->keys()) === 1 ? 'satu isian' : count($errors->keys()).' isian' }} yang perlu dibetulkan.
                        @if (session('kode_galat'))
                            <span class="block text-[0.875rem] font-normal">Kode galat: {{ session('kode_galat') }}</span>
                        @endif
                    </p>
                @endif

                <div class="space-y-6">
                    <fieldset @error('id_siswa') aria-describedby="isian-id_siswa-galat" @enderror>
                        <legend class="font-bold">Untuk anak yang mana?</legend>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            @foreach ($daftarAnak as $anak)
                                <label class="flex min-h-12 items-center gap-2 rounded-md border-2 border-pensil/20 px-3 has-[:checked]:border-tinta has-[:checked]:bg-langit/50">
                                    <input type="radio" name="id_siswa" value="{{ $anak->id_siswa }}" @checked($isi('id_siswa', $daftarAnak->count() === 1 ? $anak->id_siswa : null) === $anak->id_siswa) class="size-4 accent-tinta">
                                    {{ $anak->nama }} <span class="text-pensil-muda">({{ $anak->jenjang }})</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <x-dasbor.koreksi name="id_siswa" />

                    <x-dasbor.field name="kode_mapel" label="Mapel" hint="Pilih yang sesuai jenjang anak.">
                        <select id="isian-kode_mapel" name="kode_mapel" class="kontrol" aria-describedby="isian-kode_mapel-petunjuk @error('kode_mapel') isian-kode_mapel-galat @enderror" @error('kode_mapel') aria-invalid="true" @enderror>
                            <option value="">Pilih mapel</option>
                            @foreach ($tutor->mataPelajaran as $mapel)
                                <option value="{{ $mapel->kode_mapel }}" @selected($isi('kode_mapel') === $mapel->kode_mapel)>{{ $mapel->label() }}</option>
                            @endforeach
                        </select>
                    </x-dasbor.field>

                    <fieldset @error('id_jadwal') aria-describedby="isian-id_jadwal-galat" @enderror>
                        <legend class="font-bold">Jadwal mingguan</legend>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            @foreach ($jadwalTersedia as $jadwal)
                                <label class="flex min-h-12 items-center gap-2 rounded-md border-2 border-pensil/20 px-3 py-2 has-[:checked]:border-tinta has-[:checked]:bg-langit/50">
                                    <input type="radio" name="id_jadwal" value="{{ $jadwal->id_jadwal }}" data-mulai="{{ $mulaiTercepat[$jadwal->id_jadwal]->toDateString() }}"
                                           @checked($isi('id_jadwal') === $jadwal->id_jadwal) class="size-4 accent-tinta">
                                    <span><span class="font-bold">{{ $jadwal->label() }}</span> WIB
                                        <span class="block text-[0.8125rem] text-pensil-muda">paling cepat {{ Format::tanggal($mulaiTercepat[$jadwal->id_jadwal]) }}</span></span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <x-dasbor.koreksi name="id_jadwal" />

                    <fieldset @error('mode') aria-describedby="isian-mode-galat" @enderror>
                        <legend class="font-bold">Cara belajar</legend>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            @foreach ($tutor->profil->modes() as $mode)
                                <label class="flex min-h-12 items-center gap-2 rounded-md border-2 border-pensil/20 px-3 has-[:checked]:border-tinta has-[:checked]:bg-langit/50">
                                    <input type="radio" name="mode" value="{{ $mode->value }}" @checked($isi('mode', count($tutor->profil->modes()) === 1 ? $mode->value : null) === $mode->value) class="size-4 accent-tinta">
                                    {{ $mode->label() }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <x-dasbor.koreksi name="mode" />

                    <div class="grid gap-5 sm:grid-cols-3">
                        <x-dasbor.field name="durasi" label="Lama tiap sesi">
                            <select id="isian-durasi" name="durasi" class="kontrol" @error('durasi') aria-invalid="true" aria-describedby="isian-durasi-galat" @enderror>
                                @foreach (PesanTutorRequest::PILIHAN_DURASI as $menit)
                                    <option value="{{ $menit }}" @selected((int) $isi('durasi', 60) === $menit)>{{ $menit }} menit</option>
                                @endforeach
                            </select>
                        </x-dasbor.field>

                        <x-dasbor.field name="tanggal_mulai" label="Sesi pertama" hint="Harus hari yang sama dengan jadwal.">
                            <input id="isian-tanggal_mulai" name="tanggal_mulai" type="date" value="{{ $isi('tanggal_mulai') }}" min="{{ now()->addDay()->toDateString() }}" class="kontrol"
                                   aria-describedby="isian-tanggal_mulai-petunjuk @error('tanggal_mulai') isian-tanggal_mulai-galat @enderror" @error('tanggal_mulai') aria-invalid="true" @enderror>
                        </x-dasbor.field>

                        <x-dasbor.field name="jumlah_pertemuan" label="Jumlah pertemuan" :hint="'1 sampai '.PesanTutorRequest::MAKS_PERTEMUAN.', seminggu sekali.'">
                            <input id="isian-jumlah_pertemuan" name="jumlah_pertemuan" type="number" min="1" max="{{ PesanTutorRequest::MAKS_PERTEMUAN }}" inputmode="numeric" value="{{ $isi('jumlah_pertemuan', 4) }}" class="kontrol"
                                   aria-describedby="isian-jumlah_pertemuan-petunjuk @error('jumlah_pertemuan') isian-jumlah_pertemuan-galat @enderror" @error('jumlah_pertemuan') aria-invalid="true" @enderror>
                        </x-dasbor.field>
                    </div>
                </div>
            </x-dasbor.panel>

            <x-dasbor.panel heading="Perkiraan biaya" class="lg:sticky lg:top-8">
                <p class="text-[0.9375rem]">Tarif <span class="angka font-bold">{{ Format::rupiah($tutor->profil->tarif) }}</span> per jam, dikali lama sesi dan jumlah pertemuan.</p>
                <p class="mt-3 flex items-baseline justify-between gap-3">
                    <span class="font-bold">Perkiraan total</span>
                    <output id="perkiraan-total" class="angka font-display text-[1.6rem] font-extrabold" aria-live="polite">–</output>
                </p>
                <p class="mt-3 text-[0.875rem] text-pensil-muda">Total pasti ditampilkan di langkah berikutnya sebelum Anda mengonfirmasi. Pembayaran baru diminta setelah tutor menerima.</p>
                <button type="submit" class="tombol mt-5 w-full">Periksa pesanan</button>
            </x-dasbor.panel>
        </form>

        <script>
            (() => {
                const form = document.querySelector('form[data-tarif]');
                const keluaran = document.getElementById('perkiraan-total');
                const tarif = Number(form.dataset.tarif);
                const rupiah = (n) => 'Rp' + Math.round(n).toLocaleString('id-ID');

                const hitung = () => {
                    const durasi = Number(form.durasi.value);
                    const jumlah = Number(form.jumlah_pertemuan.value);
                    keluaran.textContent = durasi && jumlah > 0 ? rupiah(tarif * durasi / 60 * jumlah) : '–';
                };

                form.addEventListener('input', hitung);
                form.addEventListener('change', (event) => {
                    // Picking a slot fills its earliest start date if none was chosen yet.
                    if (event.target.name === 'id_jadwal' && event.target.dataset.mulai) {
                        const tanggal = form.tanggal_mulai;
                        const hariBaru = new Date(event.target.dataset.mulai).getDay();
                        if (! tanggal.value || new Date(tanggal.value).getDay() !== hariBaru) {
                            tanggal.value = event.target.dataset.mulai;
                        }
                    }
                });
                hitung();
            })();
        </script>
    @endif
</x-dasbor.layout>
