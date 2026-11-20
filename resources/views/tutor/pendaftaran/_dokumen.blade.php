@use('App\Enums\JenisDokumen')
@use('App\Enums\LangkahPendaftaran')
@use('App\Enums\StatusVerifikasi')
@use('App\Services\PendaftaranTutor')
@use('App\Support\Format')

@php
    $wajib = ! $tutor->isInternal();
    $kartu = [
        [JenisDokumen::Ijazah, $wajib ? 'Wajib' : 'Opsional', 'Ijazah pendidikan terakhir, hasil pindai atau foto yang jelas.'],
        [JenisDokumen::Ktp, $wajib ? 'Wajib' : 'Opsional', 'Foto KTP yang tidak terpotong, NIK terbaca.'],
        [JenisDokumen::Sertifikat, 'Opsional', 'Sertifikat pelatihan, lomba, atau bahasa. Boleh lebih dari satu (maks. '.PendaftaranTutor::SERTIFIKAT_MAKS.').'],
    ];
@endphp

<x-tutor.bagian nomor="3" judul="Dokumen" :perbaiki="$tutor->perluDiperbaiki(LangkahPendaftaran::Dokumen)" :catatan="$tutor->catatanPerbaikan(LangkahPendaftaran::Dokumen)">
    <p class="max-w-[40rem]">
        @if ($wajib)
            Ijazah dan KTP wajib diunggah. Format PDF, JPG, atau PNG, maksimal 2 MB per berkas. Berkas disimpan tertutup dan hanya dibuka tim akademik.
        @else
            Dokumen Anda sudah ada di bimbel, jadi bagian ini <strong>opsional</strong>. Unggah kalau ada yang baru. Format PDF, JPG, atau PNG, maksimal 2 MB.
        @endif
    </p>

    <div class="mt-7 space-y-6">
        @foreach ($kartu as [$jenis, $sifat, $petunjuk])
            @php
                $berkas = $tutor->dokumen->where('jenis', $jenis)->values();
                $bolehTambah = $jenis === JenisDokumen::Sertifikat ? $berkas->count() < PendaftaranTutor::SERTIFIKAT_MAKS : $berkas->isEmpty();
                $galatDiSini = old('jenis') === $jenis->value && $errors->has('berkas');
                $idInput = 'isian-berkas-'.$jenis->value;
            @endphp

            <section aria-labelledby="dokumen-{{ $jenis->value }}" class="border-2 border-dashed border-pensil/30 px-4 py-4 sm:px-5">
                <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                    <h3 id="dokumen-{{ $jenis->value }}" class="font-display text-[1.2rem] font-extrabold">{{ $jenis->label() }}</h3>
                    <x-dasbor.label :tone="$sifat === 'Wajib' ? 'info' : 'netral'">{{ $sifat }}</x-dasbor.label>
                </div>
                <p class="text-[0.9375rem] text-pensil-muda">{{ $petunjuk }}</p>

                @foreach ($berkas as $dokumen)
                    @php($info = $infoBerkas[$dokumen->id_dokumen] ?? ['ukuran' => null, 'ekstensi' => ''])
                    <div @class(['mt-4 flex flex-wrap items-center gap-x-4 gap-y-3 bg-kertas px-3 py-3', 'outline-2 outline-cap' => $dokumen->status_verifikasi === StatusVerifikasi::Ditolak])>
                        <span aria-hidden="true" class="grid h-12 w-10 shrink-0 place-items-center border-2 border-pensil bg-white text-[0.7rem] font-extrabold">{{ $info['ekstensi'] }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold">
                                {{ $jenis->label() }}{{ $jenis === JenisDokumen::Sertifikat ? ' '.($loop->iteration) : '' }} terunggah
                            </p>
                            <p class="text-[0.875rem] text-pensil-muda"><span class="angka">{{ $info['ekstensi'] }} · {{ Format::ukuranBerkas($info['ukuran']) }}</span> · {{ Format::tanggalPendek($dokumen->created_at) }}</p>
                            @if ($dokumen->status_verifikasi === StatusVerifikasi::Ditolak)
                                <p class="koreksi"><span><span class="font-bold">Ditolak tim akademik:</span> {{ $dokumen->alasan_penolakan }}</span></p>
                            @endif
                        </div>
                        <x-dasbor.label :tone="$dokumen->status_verifikasi->tone()">{{ $dokumen->status_verifikasi === StatusVerifikasi::Menunggu ? 'Belum diperiksa' : $dokumen->status_verifikasi->label() }}</x-dasbor.label>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('dokumen.berkas', $dokumen) }}" target="_blank" rel="noopener" class="tombol-garis min-h-10">Lihat<span class="sr-only"> {{ $jenis->label() }}</span></a>
                            <form method="POST" action="{{ route('tutor.pendaftaran.dokumen.destroy', $dokumen) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="tombol-garis tombol-bahaya min-h-10">Hapus<span class="sr-only"> {{ $jenis->label() }}</span></button>
                            </form>
                        </div>
                    </div>
                @endforeach

                @if ($bolehTambah || $jenis !== JenisDokumen::Sertifikat)
                    <form method="POST" action="{{ route('tutor.pendaftaran.dokumen.store') }}" enctype="multipart/form-data" novalidate class="mt-4">
                        @csrf
                        <input type="hidden" name="jenis" value="{{ $jenis->value }}">
                        <label for="{{ $idInput }}" class="block font-bold">
                            @if ($berkas->isNotEmpty() && $jenis !== JenisDokumen::Sertifikat)
                                Ganti berkas {{ $jenis->label() }}
                            @elseif ($jenis === JenisDokumen::Sertifikat && $berkas->isNotEmpty())
                                Tambah sertifikat lain
                            @else
                                Unggah {{ $jenis->label() }}
                            @endif
                        </label>
                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <input id="{{ $idInput }}" type="file" name="berkas" accept="application/pdf,image/jpeg,image/png" required class="unggah max-w-[24rem] flex-1"
                                @if ($galatDiSini) aria-invalid="true" aria-describedby="{{ $idInput }}-galat" @endif>
                            <button type="submit" class="tombol min-h-11">{{ $berkas->isNotEmpty() && $jenis !== JenisDokumen::Sertifikat ? 'Ganti' : 'Unggah' }}</button>
                        </div>
                        @if ($galatDiSini)
                            <x-dasbor.koreksi name="berkas" :id="$idInput.'-galat'" />
                        @endif
                    </form>
                @else
                    <p class="mt-4 text-[0.9375rem] text-pensil-muda">Sudah {{ PendaftaranTutor::SERTIFIKAT_MAKS }} sertifikat. Hapus salah satu untuk menambah yang lain.</p>
                @endif
            </section>
        @endforeach
    </div>

    <div class="mt-8">
        <a href="{{ route('tutor.pendaftaran.langkah', LangkahPendaftaran::Jadwal) }}" class="inline-flex min-h-13 items-center rounded-[0.7rem_1.1rem_0.6rem_1.2rem] bg-tinta px-8 font-display text-[1.2rem] font-bold text-white shadow-[4px_4px_0_var(--color-stabilo)]">Lanjut ke jadwal</a>
    </div>
</x-tutor.bagian>
