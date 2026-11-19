@props(['subjects', 'levels', 'locations'])

<section aria-labelledby="judul-utama" class="bg-kertas">
    <div class="mx-auto grid max-w-6xl gap-8 px-4 pt-8 pb-10 sm:px-6 md:pt-14 md:pb-16 lg:grid-cols-[1.1fr_1fr] lg:items-center lg:gap-14">
        <div>
            <h1 id="judul-utama" class="text-[2rem] leading-[1.15] font-extrabold tracking-tight text-dongker sm:text-5xl lg:text-[3.25rem]">
                Guru les privat di Kota Jambi, dipilih dan dipesan sendiri dari HP Anda.
            </h1>
            <p class="mt-4 max-w-xl text-base leading-relaxed text-abu sm:text-lg">
                Bandingkan tutor SD, SMP, dan SMA dari tarif per jam, ulasan, dan jadwal kosongnya.
                Anda baru membayar setelah tutor menerima pesanan.
            </p>
            <ul class="mt-6 hidden max-w-xl gap-x-6 gap-y-2 text-sm font-medium text-dongker sm:flex sm:flex-wrap">
                <li>Tutor diverifikasi Divisi Akademik EduLink</li>
                <li>Online atau tutor datang ke rumah</li>
            </ul>
        </div>

        <form
            action="{{ route('tutor.index') }}"
            method="GET"
            aria-labelledby="judul-cari"
            class="on-dongker rounded-lg bg-dongker p-5 text-white sm:p-7"
        >
            <h2 id="judul-cari" class="text-xl font-bold">Cari tutor</h2>
            <p class="mt-1 text-sm text-white/80">Boleh dikosongkan. Filter lain bisa diatur di halaman hasil.</p>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="cari-mapel" class="block text-sm font-semibold">Mata pelajaran</label>
                    <select id="cari-mapel" name="mapel" class="mt-1.5 block min-h-12 w-full rounded-md border-0 bg-white px-3 text-base text-dongker">
                        <option value="">Semua mata pelajaran</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject }}">{{ $subject }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="cari-jenjang" class="block text-sm font-semibold">Jenjang</label>
                    <select id="cari-jenjang" name="jenjang" class="mt-1.5 block min-h-12 w-full rounded-md border-0 bg-white px-3 text-base text-dongker">
                        <option value="">Semua jenjang</option>
                        @foreach ($levels as $key => $level)
                            <option value="{{ $key }}">{{ $level['label'] }} ({{ $level['grades'] }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="cari-lokasi" class="block text-sm font-semibold">Lokasi (kecamatan)</label>
                    <select id="cari-lokasi" name="lokasi" class="mt-1.5 block min-h-12 w-full rounded-md border-0 bg-white px-3 text-base text-dongker">
                        <option value="">Seluruh Kota Jambi</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location }}">{{ $location }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="mt-6 flex min-h-12 w-full items-center justify-center rounded-md bg-merah px-5 text-base font-bold text-white hover:bg-merah-tua active:translate-y-px">
                Tampilkan tutor
            </button>
        </form>
    </div>
</section>
