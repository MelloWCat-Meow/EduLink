{{-- Sign-out is a POST with a CSRF token, never a plain link. --}}
<form method="POST" action="{{ route('logout') }}" {{ $attributes }}>
    @csrf
    <button type="submit" class="inline-flex min-h-12 items-center rounded-[0.6rem_0.9rem_0.5rem_1rem] border-2 border-pensil bg-white px-5 font-bold hover:bg-stabilo">
        Keluar
    </button>
</form>
