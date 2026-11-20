{{-- Pagination: previous / page x of y / next. Used via ->links('components.dasbor.halaman'). --}}
@if ($paginator->hasPages())
    <nav aria-label="Halaman" class="mt-5 flex flex-wrap items-center justify-between gap-3">
        @if ($paginator->onFirstPage())
            <span class="tombol-garis opacity-40" aria-disabled="true">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="tombol-garis">Sebelumnya</a>
        @endif

        <p class="order-first w-full text-center text-[0.9375rem] sm:order-none sm:w-auto">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</p>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="tombol-garis">Berikutnya</a>
        @else
            <span class="tombol-garis opacity-40" aria-disabled="true">Berikutnya</span>
        @endif
    </nav>
@endif
