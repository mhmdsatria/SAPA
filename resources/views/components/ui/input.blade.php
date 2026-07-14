@props(['label' => null, 'name' => null, 'type' => 'text', 'hint' => null])

<div>
    @if ($label)
        <label @if($name) for="{{ $name }}" @endif class="field-label">{{ $label }}</label>
    @endif
    <input @if($name) id="{{ $name }}" name="{{ $name }}" @endif type="{{ $type }}" {{ $attributes->merge(['class' => 'field-control']) }}>
    @if ($hint)
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
    @if ($name)
        @error($name)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    @endif
</div>
