@use('App\Enums\Hari')
@use('App\Enums\LangkahPendaftaran')

@php
    $baris = old('slot', $tutor->jadwal->map(fn ($jadwal) => ['hari' => $jadwal->hari->value, 'jam' => substr($jadwal->jam, 0, 5)])->all());
    // Two blank rows to fill in, also without JavaScript.
    $baris = array_values(array_merge($baris, [['hari' => '', 'jam' => ''], ['hari' => '', 'jam' => '']]));
@endphp

<x-tutor.bagian nomor="4" judul="Jadwal ketersediaan" :perbaiki="$tutor->perluDiperbaiki(LangkahPendaftaran::Jadwal)" :catatan="$tutor->catatanPerbaikan(LangkahPendaftaran::Jadwal)">
    <p class="max-w-[40rem]">Tulis slot mingguan saat Anda bisa mengajar: hari dan jam mulai. Orang tua memesan dari slot ini. Minimal satu slot. Kosongkan baris untuk menghapusnya.</p>

    <form method="POST" action="{{ route('tutor.pendaftaran.jadwal') }}" novalidate class="mt-6" data-jadwal>
        @csrf
        @method('PUT')
        <x-dasbor.koreksi name="slot" id="isian-slot-galat" />

        <table class="mt-2 w-full max-w-[34rem] border-collapse">
            <thead>
                <tr class="border-b-2 border-pensil text-left">
                    <th scope="col" class="w-10 py-2 font-bold">No.</th>
                    <th scope="col" class="py-2 font-bold">Hari</th>
                    <th scope="col" class="py-2 font-bold">Jam mulai</th>
                    <th scope="col" class="w-12 py-2"><span class="sr-only">Hapus</span></th>
                </tr>
            </thead>
            <tbody data-baris-induk>
                @foreach ($baris as $i => $slot)
                    @include('tutor.pendaftaran._baris-jadwal', ['i' => $i, 'slot' => $slot])
                @endforeach
            </tbody>
        </table>

        <template data-templat-baris>
            @include('tutor.pendaftaran._baris-jadwal', ['i' => '__I__', 'slot' => ['hari' => '', 'jam' => '']])
        </template>

        <button type="button" class="tombol-garis mt-4" data-tambah-baris hidden>
            <svg aria-hidden="true" class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M10 4v12M4 10h12"/></svg>
            Tambah baris
        </button>

        <div class="mt-8">@include('tutor.pendaftaran._tombol')</div>
    </form>
</x-tutor.bagian>

<script>
    (() => {
        const form = document.querySelector('[data-jadwal]');
        if (!form) return;
        const induk = form.querySelector('[data-baris-induk]');
        const templat = form.querySelector('[data-templat-baris]');
        const tombol = form.querySelector('[data-tambah-baris]');
        let berikut = induk.children.length;
        const nomori = () => [...induk.children].forEach((tr, n) => (tr.querySelector('[data-nomor]').textContent = n + 1));
        tombol.hidden = false;
        tombol.addEventListener('click', () => {
            induk.insertAdjacentHTML('beforeend', templat.innerHTML.replaceAll('__I__', berikut++));
            nomori();
            induk.lastElementChild.querySelector('select').focus();
        });
        induk.addEventListener('click', (e) => {
            const hapus = e.target.closest('[data-hapus-baris]');
            if (!hapus) return;
            const tr = hapus.closest('tr');
            tr.querySelectorAll('select, input').forEach((el) => (el.value = ''));
            if (induk.children.length > 1) tr.remove();
            nomori();
        });
    })();
</script>
