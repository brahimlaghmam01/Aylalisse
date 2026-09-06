@props(['label', 'name', 'type' => 'text', 'value' => null, 'required' => false, 'hint' => null])

<div>
    <label for="{{ $name }}" class="admin-label">{{ $label }}@if($required) *@endif</label>
    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'admin-input '.($errors->has($name) ? 'has-error' : '')]) }}
    >
    @if ($hint)
        <p class="mt-1 text-xs text-ink/45">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
