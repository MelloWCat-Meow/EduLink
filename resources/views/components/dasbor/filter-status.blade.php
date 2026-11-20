@props(['route', 'dipilih' => null, 'params' => []])

{{-- Status filter as plain links, so it works without JavaScript and keeps the URL shareable. --}}
<nav aria-label="Saring menurut status" class="mb-5 flex gap-2 overflow-x-auto pb-1">
    @foreach ([null, ...\App\Enums\StatusPesanan::cases()] as $status)
        @php($aktif = $dipilih === $status)
        <a href="{{ route($route, array_filter([...$params, 'status' => $status?->value])) }}" @if ($aktif) aria-current="page" @endif
           @class([
               'inline-flex min-h-10 shrink-0 items-center rounded-full border-2 px-3.5 text-[0.9375rem] font-bold whitespace-nowrap',
               'border-tinta bg-tinta text-white' => $aktif,
               'border-pensil/25 bg-white hover:border-tinta' => ! $aktif,
           ])>{{ $status?->label() ?? 'Semua' }}</a>
    @endforeach
</nav>
