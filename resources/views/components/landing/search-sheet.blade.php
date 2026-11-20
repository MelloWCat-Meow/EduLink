@props(['subjects', 'levels', 'locations'])

<form
    action="{{ route('tutor.index') }}"
    method="get"
    aria-labelledby="judul-lembar"
    class="relative rotate-[-0.8deg] bg-white px-6 pt-6 pb-7 shadow-[0_1px_0_rgb(0_0_0/0.04),0_22px_40px_-28px_rgb(37_37_43/0.55)] sm:px-10 sm:pt-7"
>
    {{-- Torn top edge of a sheet pulled from a pad. --}}
    <div aria-hidden="true" class="absolute inset-x-0 -top-2 h-2 bg-white [clip-path:polygon(0_100%,3%_30%,7%_80%,12%_20%,18%_70%,24%_10%,31%_60%,37%_25%,44%_85%,51%_30%,58%_75%,64%_15%,71%_65%,78%_20%,85%_80%,91%_35%,96%_70%,100%_25%,100%_100%)]"></div>

    <div class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1 border-b-2 border-pensil pb-3">
        <h2 id="judul-lembar" class="font-display text-[1.6rem] font-extrabold tracking-tight [font-stretch:85%]">Lembar cari guru</h2>
        <p class="font-tangan text-[1.1rem] text-pensil-muda">dikerjakan oleh: orang tua</p>
    </div>

    <ol class="mt-6 space-y-7">
        <li>
            <label for="cari-mapel" class="block font-bold">1. Mapel apa yang mau dikejar?</label>
            <select id="cari-mapel" name="mapel" class="isian mt-1">
                <option value="">semua mapel</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject }}">{{ $subject }}</option>
                @endforeach
            </select>
        </li>

        <li>
            <fieldset>
                <legend class="font-bold">2. Lingkari jenjangnya.</legend>
                <div class="mt-2 flex flex-wrap gap-x-5 gap-y-2">
                    @foreach ($levels as $key => $level)
                        <label class="relative">
                            <input type="radio" name="jenjang" value="{{ $key }}" class="pilihan-jenjang sr-only">
                            <span class="tanda-jenjang relative inline-flex min-h-12 min-w-16 items-center justify-center px-3 font-tangan text-[1.6rem] font-bold text-tinta">
                                {{ $level['label'] }}
                                <span class="sr-only">({{ $level['grades'] }})</span>
                                <svg aria-hidden="true" class="pointer-events-none absolute inset-0 size-full overflow-visible text-margin" viewBox="0 0 100 50" preserveAspectRatio="none" fill="none">
                                    <path class="lingkaran" pathLength="1" d="M58 6C30 3 6 11 5 25c-1 15 26 21 50 20 25-1 42-9 40-22C93 10 72 4 44 7" stroke="currentColor" stroke-width="3" stroke-linecap="round" vector-effect="non-scaling-stroke" />
                                </svg>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        </li>

        <li>
            <label for="cari-lokasi" class="block font-bold">3. Rumah Anda di kecamatan mana?</label>
            <select id="cari-lokasi" name="lokasi" class="isian mt-1">
                <option value="">seluruh Kota Jambi</option>
                @foreach ($locations as $location)
                    <option value="{{ $location }}">{{ $location }}</option>
                @endforeach
            </select>
        </li>
    </ol>

    <div class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-3">
        <button type="submit" class="inline-flex min-h-13 items-center rounded-[0.7rem_1.1rem_0.6rem_1.2rem] bg-tinta px-7 font-display text-[1.25rem] font-bold text-white shadow-[4px_4px_0_var(--color-stabilo)] transition-[translate,box-shadow] hover:-translate-y-0.5 hover:shadow-[6px_6px_0_var(--color-stabilo)] active:translate-y-0 active:shadow-[2px_2px_0_var(--color-stabilo)] motion-reduce:transition-none">
            Tampilkan tutor
        </button>
        <p class="text-[0.9375rem] text-pensil-muda">Kolom boleh dikosongkan.</p>
    </div>
</form>
