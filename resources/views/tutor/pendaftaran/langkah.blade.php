@use('App\Enums\LangkahPendaftaran')
@use('App\Enums\StatusVerifikasiTutor')

{{--
    The tutor application sheet. Visitors only ever see step 1 (creating the
    account); every later step needs the account so it can be saved as a draft.
--}}
@php
    $internal = $tutor?->isInternal() ?? false;
    $perluPerbaikan = $tutor?->status_verifikasi === StatusVerifikasiTutor::PerluPerbaikan;
@endphp

<x-tutor.halaman :title="$langkah->label().' · Pendaftaran tutor'">
    <div class="mb-7 flex flex-wrap items-end justify-between gap-x-8 gap-y-3">
        <div>
            <h1 class="font-display text-[clamp(2rem,5vw,3.2rem)] leading-[1] font-extrabold tracking-[-0.025em] [font-stretch:88%]">
                Lembar <span class="stabilo">lamaran tutor</span>
            </h1>
            <p class="mt-2 font-tangan text-[1.2rem] text-pensil-muda">
                @if ($tutor === null)
                    diisi oleh: calon tutor · tersimpan per langkah, bisa dilanjutkan nanti
                @elseif ($internal)
                    diisi oleh: pengajar bimbel EduLink · data dari bimbel sudah terisi
                @else
                    diisi oleh: {{ $tutor->nama }} · tersimpan per langkah, bisa dilanjutkan nanti
                @endif
            </p>
        </div>
        @if ($tutor)
            <p class="text-[0.9375rem]">No. lamaran <span class="angka font-bold">{{ $tutor->id_tutor }}</span></p>
        @endif
    </div>

    @if ($perluPerbaikan && filled($tutor->catatan_verifikasi))
        <x-tutor.koreksi-admin :catatan="$tutor->catatan_verifikasi" class="mb-8 max-w-[44rem]" />
    @endif

    <x-tutor.penanda-langkah :langkah="$langkah" :tutor="$tutor" :kekurangan="$kekurangan" />

    <div class="relative bg-white px-5 pt-7 pb-9 shadow-[0_1px_0_rgb(0_0_0/0.04),0_22px_40px_-28px_rgb(37_37_43/0.55)] sm:px-9">
        @include('tutor.pendaftaran._'.$langkah->value)
    </div>

    @if ($tutor === null)
        <p class="mt-8 flex max-w-[34rem] items-start gap-2 font-tangan text-[1.25rem] leading-snug text-tinta">
            <x-landing.tick class="mt-0.5 size-7 shrink-0" />
            <span>Sudah mengajar di bimbel EduLink? <a href="{{ route('tutor.aktivasi') }}" class="underline decoration-2 underline-offset-4">Aktifkan akun</a>, data Anda tidak perlu diisi ulang.</span>
        </p>
    @endif
</x-tutor.halaman>
