@props(['label', 'name' => null])

<label class="flex cursor-pointer items-start gap-3">
    <input @if($name) name="{{ $name }}" @endif type="checkbox" {{ $attributes->merge(['class' => 'mt-0.5 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500']) }}>
    <span class="text-sm text-slate-700 dark:text-slate-200">{{ $label }}</span>
</label>
