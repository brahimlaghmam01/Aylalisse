@props([
    'eyebrow' => null,
    'title' => null,
    'align' => 'left', // left | center
])

<div {{ $attributes->class([
    'max-w-2xl',
    'mx-auto text-center' => $align === 'center',
]) }}>
    @if ($eyebrow)
        <p class="eyebrow">{{ $eyebrow }}</p>
    @endif

    @if ($title)
        <h2 class="display-2 mt-4 text-balance">{{ $title }}</h2>
    @endif

    @if (! $slot->isEmpty())
        <div class="mt-5 text-ink/70 text-balance">{{ $slot }}</div>
    @endif
</div>
