@props(['variant' => 'default'])
@php
    $color = $variant === 'light' ? '#E6D5C8' : '#A98674';
@endphp
<svg {{ $attributes }} viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path
        d="M24 6 C34 6 40 14 38 24 C36 32 28 38 18 36 C10 34 6 26 10 18 C12 14 16 12 20 14 C22 15 22 18 20 19 C18 20 16 18 17 16"
        stroke="{{ $color }}" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"
    />
</svg>
