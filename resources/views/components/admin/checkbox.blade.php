@props(['label', 'name', 'checked' => false])

<label class="flex items-center gap-2.5 text-sm text-ink/80">
    <input
        type="checkbox"
        name="{{ $name }}"
        value="1"
        @checked(old($name, $checked))
        class="h-4 w-4 accent-cocoa"
    >
    {{ $label }}
</label>
