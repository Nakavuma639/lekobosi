@props(['field'])

@error($field)
    <p {{ $attributes->merge(['class' => 'form-error']) }}>{{ $message }}</p>
@enderror
