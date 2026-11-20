@props(['href', 'kini' => false])
<a href="{{ $href }}" {{ $attributes->merge(['class' => 'hover:bg-langit/60']) }}>{{ $slot }}</a>
