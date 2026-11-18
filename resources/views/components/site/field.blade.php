@props([
    'name',
    'label',
    'type' => 'text',
    'hint' => null,
    'value' => null,
])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
@endphp

<div>
    <label for="{{ $id }}" class="field-label">
        {{ $label }}
        @unless ($attributes->has('required'))
            <span class="font-normal text-stone-500">(optional)</span>
        @endunless
    </label>

    @if ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}"
            @if ($hint) aria-describedby="{{ $id }}-hint" @endif
            @if ($hasError) aria-invalid="true" @endif
            {{ $attributes->except('id')->merge(['class' => 'field-input', 'rows' => 4]) }}>{{ old($name, $value) }}</textarea>
    @elseif ($type === 'select')
        <select id="{{ $id }}" name="{{ $name }}"
            @if ($hasError) aria-invalid="true" @endif
            {{ $attributes->except('id')->merge(['class' => 'field-input']) }}>
            {{ $slot }}
        </select>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
            @unless ($type === 'password') value="{{ old($name, $value) }}" @endunless
            @if ($hint) aria-describedby="{{ $id }}-hint" @endif
            @if ($hasError) aria-invalid="true" @endif
            {{ $attributes->except('id')->merge(['class' => 'field-input']) }}>
    @endif

    @if ($hint)
        <p id="{{ $id }}-hint" class="field-hint">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
