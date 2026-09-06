@props(['label', 'name', 'value' => null, 'required' => false, 'rows' => 4])

<div>
    <label for="{{ $name }}" class="admin-label">{{ $label }}@if($required) *@endif</label>
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'admin-input '.($errors->has($name) ? 'has-error' : '')]) }}
    >{{ old($name, $value) }}</textarea>
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
