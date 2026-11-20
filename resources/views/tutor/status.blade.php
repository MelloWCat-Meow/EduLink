@use('App\Enums\StatusVerifikasiTutor')
@use('App\Support\Format')

@php($ditolak = $tutor->status_verifikasi === StatusVerifikasiTutor::Ditolak)

<x-tutor.halaman :title="$ditolak ? 'Pendaftaran tidak diterima' : 'Pendaftaran sedang diperiksa'">
    <div class="mb-7">
        <h1 class="font-display text-[clamp(2rem,5vw,3.2rem)] leading-[1.02] font-extrabold tracking-[-0.025em] [font-stretch:88%]">
            @if ($ditolak)
                Pendaftaran <span class="stabilo">belum diterima</span>
            @else
                Sedang diperiksa <span class="stabilo">tim akademik</span>
            @endif
        </h1>
        <p class="mt-3 flex flex-wrap items-center gap-3">
            <x-dasbor.label :tone="$tutor->status_verifikasi->tone()">{{ $tutor->status_verifikasi->label() }}</x-dasbor.label>
            <span class="text-[0.9375rem] text-pensil-muda">No. lamaran <span class="angka font-bold text-pensil">{{ $tutor->id_tutor }}</span> · dikirim {{ Format::waktu($tutor->diajukan_pada) }}</span>
        </p>
    </div>

    @if ($ditolak)
        <x-tutor.koreksi-admin :catatan="$tutor->catatan_verifikasi" class="mb-6 max-w-[44rem]" />
        <p class="max-w-[44rem]">Keputusan ini final, jadi pendaftaran tidak bisa diajukan ulang dengan akun ini. Kalau menurut Anda ada kekeliruan, hubungi admin EduLink dan sebutkan nomor lamaran di atas.</p>
    @else
        <p class="max-w-[44rem]">Tim akademik memeriksa identitas, dokumen, kompetensi, dan kesesuaian mata pelajaran. Hasilnya dikirim ke <span class="font-bold">{{ $tutor->kontak }}</span>. Selama diperiksa, isian di bawah tidak bisa diubah.</p>

        <div class="mt-8 bg-white px-5 pt-6 pb-8 shadow-[0_1px_0_rgb(0_0_0/0.04),0_22px_40px_-28px_rgb(37_37_43/0.55)] sm:px-9">
            <h2 class="mb-6 font-display text-[1.45rem] font-extrabold">Isian yang dikirim</h2>
            @include('tutor.pendaftaran._ringkasan', ['bisaUbah' => false])
        </div>
    @endif
</x-tutor.halaman>
