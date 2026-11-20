@use('App\Support\Format')
@use('App\Enums\Peran')
@use('App\Enums\StatusVerifikasiTutor')

@php
    $akun = auth()->user();
    // A tutor whose application is not verified yet has no dashboard; they keep the sign-up shell.
    $tanpaDasbor = $akun->hasPeran(Peran::Tutor) && $akun->tutor?->status_verifikasi !== StatusVerifikasiTutor::Terverifikasi;
@endphp

<x-dynamic-component :component="$tanpaDasbor ? 'tutor.halaman' : 'dasbor.layout'" title="Notifikasi">
    <x-dasbor.judul title="Notifikasi" :note="$belumDibaca > 0 ? $belumDibaca.' belum dibaca' : 'semua sudah dibaca'">
        @if ($belumDibaca > 0)
            <x-slot:actions>
                <form method="post" action="{{ route('notifikasi.baca-semua') }}">
                    @csrf
                    <button type="submit" class="tombol-garis">Tandai semua sudah dibaca</button>
                </form>
            </x-slot:actions>
        @endif
    </x-dasbor.judul>

    <x-dasbor.panel>
        @if ($daftarNotifikasi->isEmpty())
            <x-dasbor.kosong message="Belum ada notifikasi." />
        @else
            <ul class="divide-y divide-pensil/12">
                @foreach ($daftarNotifikasi as $notifikasi)
                    @php($baru = $notifikasi->read_at === null)
                    <li @class(['flex flex-wrap items-start justify-between gap-x-6 gap-y-2 py-4', 'bg-langit/35 -mx-3 px-3' => $baru])>
                        <div class="min-w-0 flex-1">
                            <p class="flex flex-wrap items-center gap-2">
                                @if ($baru)
                                    <x-dasbor.label tone="info">Baru</x-dasbor.label>
                                @endif
                                <span class="font-bold">{{ $notifikasi->data['judul'] ?? 'Notifikasi' }}</span>
                            </p>
                            <p class="mt-0.5">{{ $notifikasi->data['pesan'] ?? '' }}</p>
                            <p class="mt-0.5 text-[0.8125rem] text-pensil-muda">{{ Format::waktu($notifikasi->created_at) }} · {{ $notifikasi->created_at->diffForHumans() }}</p>
                        </div>
                        <form method="post" action="{{ route('notifikasi.buka', $notifikasi->id) }}">
                            @csrf
                            <button type="submit" class="tombol-garis min-h-10">{{ $baru ? 'Buka dan tandai dibaca' : 'Buka' }}<span class="sr-only">: {{ $notifikasi->data['judul'] ?? '' }}</span></button>
                        </form>
                    </li>
                @endforeach
            </ul>
            {{ $daftarNotifikasi->links('components.dasbor.halaman') }}
        @endif
    </x-dasbor.panel>
</x-dynamic-component>
