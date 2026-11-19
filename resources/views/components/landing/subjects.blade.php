@props(['levels'])

<section id="jenjang" aria-labelledby="judul-jenjang" class="mx-auto max-w-6xl px-4 py-12 sm:px-6 md:py-16">
    <h2 id="judul-jenjang" class="text-2xl font-extrabold tracking-tight sm:text-3xl">Sudah tahu yang dicari?</h2>
    <p class="mt-2 max-w-2xl text-abu">Pilih jenjang dan mata pelajaran untuk langsung melihat tutornya.</p>

    <div class="mt-8 grid gap-6 md:grid-cols-3">
        @foreach ($levels as $key => $level)
            <div @class([
                'border-t-4 pt-4',
                'border-merah' => $key === 'sd',
                'border-dongker' => $key === 'smp',
                'border-abu' => $key === 'sma',
            ])>
                <h3 class="flex items-baseline gap-2">
                    <span class="text-xl font-extrabold">{{ $level['label'] }}</span>
                    <span class="text-sm text-abu">{{ $level['grades'] }}</span>
                </h3>
                <ul class="mt-3 flex flex-wrap gap-2">
                    @foreach ($level['subjects'] as $subject)
                        <li>
                            <a
                                href="{{ route('tutor.index', ['mapel' => $subject, 'jenjang' => $key]) }}"
                                class="inline-flex min-h-11 items-center rounded-md border border-garis bg-white px-3.5 text-sm font-medium text-dongker hover:border-dongker hover:bg-kertas active:bg-garis"
                            >{{ $subject }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</section>
