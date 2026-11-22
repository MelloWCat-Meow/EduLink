@props(['href' => null, 'kini' => false])
<span @if ($kini) aria-current="step" @endif {{ $attributes }}>{{ $slot }}</span>
