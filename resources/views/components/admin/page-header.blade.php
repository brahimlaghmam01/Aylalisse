@props(['title', 'subtitle' => null])

<div class="mb-8 flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="font-serif text-2xl text-cocoa">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-ink/55">{{ $subtitle }}</p>
        @endif
    </div>
    @if (isset($actions))
        <div class="flex items-center gap-3">{{ $actions }}</div>
    @endif
</div>
