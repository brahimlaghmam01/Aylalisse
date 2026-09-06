@props(['title' => null])

<div {{ $attributes->class('admin-card') }}>
    @if ($title)
        <div class="border-b border-nude/30 px-6 py-4">
            <h2 class="font-serif text-lg text-cocoa">{{ $title }}</h2>
        </div>
    @endif
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
