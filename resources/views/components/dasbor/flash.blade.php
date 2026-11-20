@if (session('status'))
    <p role="status" class="mb-6 flex items-start gap-2 border-l-4 border-tinta bg-langit/70 px-4 py-3 font-bold">
        <svg aria-hidden="true" class="mt-0.5 size-5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg>
        {{ session('status') }}
    </p>
@endif

@if (session('galat'))
    <p role="alert" class="mb-6 flex items-start gap-2 border-l-4 border-cap bg-jambu/60 px-4 py-3 font-bold text-pensil">
        <svg aria-hidden="true" class="mt-0.5 size-5 shrink-0 text-cap" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M10 4v7.5M10 15.5v.5"/></svg>
        <span>
            {{ session('galat') }}
            @if (session('kode_galat'))
                <span class="block text-[0.875rem] font-normal text-pensil-muda">Kode galat: {{ session('kode_galat') }}</span>
            @endif
        </span>
    </p>
@endif
