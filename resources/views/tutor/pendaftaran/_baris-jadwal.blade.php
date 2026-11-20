@use('App\Enums\Hari')

<tr class="border-b border-pensil/15 align-top">
    <td class="py-3 font-tangan text-[1.25rem] text-tinta" data-nomor>{{ is_int($i) ? $i + 1 : '' }}</td>
    <td class="py-2 pr-3">
        <label for="slot-{{ $i }}-hari" class="sr-only">Hari, baris {{ is_int($i) ? $i + 1 : '' }}</label>
        <select id="slot-{{ $i }}-hari" name="slot[{{ $i }}][hari]" class="isian" @error("slot.$i.hari") aria-invalid="true" aria-describedby="slot-{{ $i }}-hari-galat" @enderror>
            <option value="">Pilih hari</option>
            @foreach (Hari::cases() as $hari)
                <option value="{{ $hari->value }}" @selected(($slot['hari'] ?? '') === $hari->value)>{{ $hari->label() }}</option>
            @endforeach
        </select>
        @if (is_int($i))
            <x-dasbor.koreksi :name="'slot.'.$i.'.hari'" :id="'slot-'.$i.'-hari-galat'" />
        @endif
    </td>
    <td class="py-2 pr-3">
        <label for="slot-{{ $i }}-jam" class="sr-only">Jam mulai, baris {{ is_int($i) ? $i + 1 : '' }}</label>
        <input id="slot-{{ $i }}-jam" type="time" name="slot[{{ $i }}][jam]" value="{{ $slot['jam'] ?? '' }}" min="06:00" max="21:00" step="900" class="isian-teks max-w-[9rem]"
            @error("slot.$i.jam") aria-invalid="true" aria-describedby="slot-{{ $i }}-jam-galat" @enderror>
        @if (is_int($i))
            <x-dasbor.koreksi :name="'slot.'.$i.'.jam'" :id="'slot-'.$i.'-jam-galat'" />
        @endif
    </td>
    <td class="py-2">
        <button type="button" data-hapus-baris class="grid size-11 place-items-center rounded-md text-cap hover:bg-jambu/50" aria-label="Hapus baris">
            <svg aria-hidden="true" class="size-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M5 5l10 10M15 5L5 15"/></svg>
        </button>
    </td>
</tr>
