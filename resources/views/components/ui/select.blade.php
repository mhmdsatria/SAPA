@props(['label' => null, 'name' => null])

<div>
    @if ($label)
        <label @if($name) for="{{ $name }}" @endif class="field-label">{{ $label }}</label>
    @endif
    <select @if($name) id="{{ $name }}" name="{{ $name }}" @endif {{ $attributes->merge(['class' => 'field-control']) }}>{{ $slot }}</select>
    @if ($name)
        @error($name)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    @endif
</div>
