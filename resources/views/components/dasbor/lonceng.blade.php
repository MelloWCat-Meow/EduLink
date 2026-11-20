{{-- Bell with the unread count. The count is also in the link text for screen readers. --}}
@php($belumDibaca = auth()->user()->unreadNotifications()->count())

<a href="{{ route('notifikasi.index') }}" @if (request()->routeIs('notifikasi.*')) aria-current="page" @endif
   {{ $attributes->merge(['class' => 'relative inline-flex size-11 shrink-0 items-center justify-center rounded-md border-2 border-pensil/25 bg-white hover:bg-langit/60']) }}>
    <svg aria-hidden="true" class="size-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8a5 5 0 0 1 10 0c0 4 1.5 5.5 1.5 5.5h-13S5 12 5 8zM8.3 16.5a1.8 1.8 0 0 0 3.4 0"/></svg>
    <span class="sr-only">Notifikasi{{ $belumDibaca > 0 ? ', '.$belumDibaca.' belum dibaca' : '' }}</span>
    @if ($belumDibaca > 0)
        <span aria-hidden="true" class="angka absolute -top-2 -right-2 grid h-6 min-w-6 place-items-center rounded-full bg-cap px-1.5 text-[0.75rem] font-bold text-white">{{ $belumDibaca > 99 ? '99+' : $belumDibaca }}</span>
    @endif
</a>
